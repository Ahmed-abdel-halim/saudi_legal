<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Services\Legal\LegalSearchService;

/**
 * Azure AI Search Service
 * ─────────────────────────────────────────────────────────────────────────────
 * يدير البحث الذكي (Vector + Hybrid) في Azure AI Search
 * يستبدل LegalSearchService تدريجياً عبر feature flag
 * ─────────────────────────────────────────────────────────────────────────────
 */
class AzureSearchService
{
    protected string $endpoint;
    protected string $apiKey;
    protected string $indexName;
    protected string $apiVersion = '2024-05-01-preview';

    // Gemini embedding endpoint
    protected string $geminiKey;
    protected string $embeddingModel = 'gemini-embedding-2';

    public function __construct()
    {
        $this->endpoint  = rtrim(config('azure.search.endpoint', ''), '/');
        $this->apiKey    = config('azure.search.key', '');
        $this->indexName = config('azure.search.index', 'legal-documents');
        $this->geminiKey = config('services.gemini.key', env('GEMINI_API_KEY'));
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  PUBLIC API
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Hybrid Search (Vector + Keyword) — الطريقة الرئيسية
     *
     * @param  string  $query    السؤال القانوني بالعربية
     * @param  int     $topK     عدد النتائج
     * @param  array   $filters  فلاتر اختيارية ['domain' => 'family', 'source_type' => 'article']
     */
    public function hybridSearch(string $query, int $topK = 5, array $filters = []): Collection
    {
        if (! $this->isConfigured()) {
            Log::warning('[AzureSearch] Not configured — falling back to keyword search.');
            return $this->fallbackToKeywordSearch($query, $topK);
        }

        try {
            // 1. توليد embedding للسؤال (مع cache لتوفير API calls)
            $embedding = $this->getEmbedding($query);

            // 2. بناء الـ filter string لـ Azure OData
            $filterStr = $this->buildFilter($filters);

            // 3. إرسال طلب Hybrid Search
            $response = Http::withHeaders([
                'api-key'      => $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post(
                "{$this->endpoint}/indexes/{$this->indexName}/docs/search?api-version={$this->apiVersion}",
                $this->buildHybridSearchBody($query, $embedding, $topK, $filterStr)
            );

            if (! $response->successful()) {
                Log::error('[AzureSearch] Search failed', [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);
                return $this->fallbackToKeywordSearch($query, $topK);
            }

            return $this->parseSearchResults($response->json());

        } catch (\Throwable $e) {
            Log::error('[AzureSearch] Exception: ' . $e->getMessage());
            return $this->fallbackToKeywordSearch($query, $topK);
        }
    }

    /**
     * Vector-only Search (أدق للمعنى الدلالي)
     */
    public function vectorSearch(string $query, int $topK = 5, array $filters = []): Collection
    {
        if (! $this->isConfigured()) {
            return $this->fallbackToKeywordSearch($query, $topK);
        }

        try {
            $embedding = $this->getEmbedding($query);
            $filterStr = $this->buildFilter($filters);

            $response = Http::withHeaders([
                'api-key'      => $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post(
                "{$this->endpoint}/indexes/{$this->indexName}/docs/search?api-version={$this->apiVersion}",
                [
                    'count'  => true,
                    'select' => 'id,question,answer,domain,source_type,case_reference,law_system',
                    'filter' => $filterStr ?: null,
                    'vectorQueries' => [
                        [
                            'kind'    => 'vector',
                            'vector'  => $embedding,
                            'fields'  => 'embedding',
                            'k'       => $topK,
                            'exhaustive' => true,
                        ]
                    ],
                ]
            );

            if (! $response->successful()) {
                return $this->fallbackToKeywordSearch($query, $topK);
            }

            return $this->parseSearchResults($response->json());

        } catch (\Throwable $e) {
            Log::error('[AzureSearch] VectorSearch Exception: ' . $e->getMessage());
            return $this->fallbackToKeywordSearch($query, $topK);
        }
    }

    /**
     * فهرسة وثيقة واحدة في Azure AI Search
     */
    public function indexDocument(array $document): bool
    {
        if (! $this->isConfigured()) {
            return false;
        }

        // توليد embedding للنص الكامل
        $textToEmbed = implode(' ', array_filter([
            $document['question']    ?? '',
            $document['answer']      ?? '',
            $document['case_text']   ?? '',
        ]));

        $document['embedding'] = $this->getEmbedding($textToEmbed);

        try {
            $response = Http::withHeaders([
                'api-key'      => $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post(
                "{$this->endpoint}/indexes/{$this->indexName}/docs/index?api-version={$this->apiVersion}",
                [
                    'value' => [
                        array_merge($document, ['@search.action' => 'mergeOrUpload'])
                    ]
                ]
            );

            return $response->successful();

        } catch (\Throwable $e) {
            Log::error('[AzureSearch] IndexDocument Exception: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * فهرسة دفعة من الوثائق (batch)
     */
    public function indexBatch(array $documents): array
    {
        if (! $this->isConfigured()) {
            return ['success' => 0, 'failed' => count($documents)];
        }

        $indexed = 0;
        $failed  = 0;

        // Azure يقبل max 1000 document per batch
        // نقوم بتقليل حجم الـ chunk لـ 50 لتوافق حد الـ Batch Embeddings الخاص بـ Gemini
        $chunks = array_chunk($documents, 50);

        foreach ($chunks as $chunk) {
            $texts = [];
            foreach ($chunk as $doc) {
                $texts[] = implode(' ', array_filter([
                    $doc['question']  ?? '',
                    $doc['answer']    ?? '',
                    $doc['case_text'] ?? '',
                ]));
            }

            // توليد الـ embeddings دفعة واحدة لتسريع الفهرسة 40 ضعفاً!
            $vectors = $this->getEmbeddingsBatch($texts);

            $batch = [];
            foreach ($chunk as $i => $doc) {
                $doc['embedding']      = $vectors[$i];
                $doc['@search.action'] = 'mergeOrUpload';
                $batch[] = $doc;
            }

            $maxAzureRetries = 3;
            $azureRetryDelay = 2; // ثوانٍ
            $response = null;

            for ($attempt = 1; $attempt <= $maxAzureRetries; $attempt++) {
                try {
                    $response = Http::withHeaders([
                        'api-key'      => $this->apiKey,
                        'Content-Type' => 'application/json',
                    ])
                    ->timeout(90) // مهلة 90 ثانية لرفع الحزم الكبيرة (مثل الأحكام القضائية الطويلة)
                    ->post(
                        "{$this->endpoint}/indexes/{$this->indexName}/docs/index?api-version={$this->apiVersion}",
                        ['value' => $batch]
                    );

                    if ($response->successful()) {
                        break; // نجاح الطلب
                    }

                    Log::warning("[AzureSearch] Azure Upload failed (Attempt {$attempt}/{$maxAzureRetries})", [
                        'status' => $response->status(),
                        'body' => $response->body()
                    ]);

                } catch (\Throwable $e) {
                    Log::warning("[AzureSearch] Azure Upload exception (Attempt {$attempt}/{$maxAzureRetries}): " . $e->getMessage());
                }

                if ($attempt < $maxAzureRetries) {
                    sleep($azureRetryDelay);
                    $azureRetryDelay *= 2; // مضاعفة وقت التأخير
                }
            }

            if ($response && $response->successful()) {
                $indexed += count($batch);
            } else {
                $failed += count($batch);
                Log::error('[AzureSearch] Batch failed permanently after retries');
            }
        }

        return ['success' => $indexed, 'failed' => $failed];
    }

    /**
     * إنشاء الـ index في Azure AI Search (تشغيل مرة واحدة)
     */
    public function createIndex(): bool
    {
        if (! $this->isConfigured()) {
            return false;
        }

        $schema = $this->getLegalIndexSchema();

        try {
            $response = Http::withHeaders([
                'api-key'      => $this->apiKey,
                'Content-Type' => 'application/json',
            ])->put(
                "{$this->endpoint}/indexes/{$this->indexName}?api-version={$this->apiVersion}",
                $schema
            );

            if ($response->successful()) {
                Log::info("[AzureSearch] Index '{$this->indexName}' created successfully.");
                return true;
            }

            Log::error('[AzureSearch] Create index failed', ['body' => $response->body()]);
            return false;

        } catch (\Throwable $e) {
            Log::error('[AzureSearch] CreateIndex Exception: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * فحص حالة الـ index
     */
    public function getIndexStats(): ?array
    {
        if (! $this->isConfigured()) {
            return null;
        }

        try {
            $response = Http::withHeaders(['api-key' => $this->apiKey])
                ->get("{$this->endpoint}/indexes/{$this->indexName}/stats?api-version={$this->apiVersion}");

            return $response->successful() ? $response->json() : null;

        } catch (\Throwable $e) {
            return null;
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  PRIVATE HELPERS
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * توليد embedding عبر Gemini API مع cache (24 ساعة)
     */
    private function getEmbedding(string $text): array
    {
        $cacheKey = 'embed_' . md5($text);

        return Cache::remember($cacheKey, now()->addHours(24), function () use ($text) {
            // تقليم النص لحد Gemini (max ~2000 tokens)
            $text = mb_substr($text, 0, 5000);

            $response = Http::withHeaders(['Content-Type' => 'application/json'])
                ->post(
                    "https://generativelanguage.googleapis.com/v1beta/models/{$this->embeddingModel}:embedContent?key={$this->geminiKey}",
                    [
                        'model'   => "models/{$this->embeddingModel}",
                        'content' => ['parts' => [['text' => $text]]],
                        'taskType' => 'RETRIEVAL_QUERY',
                        'outputDimensionality' => 768,
                    ]
                );

            if ($response->successful()) {
                return $response->json('embedding.values', []);
            }

            Log::warning('[AzureSearch] Embedding failed', ['status' => $response->status()]);
            return array_fill(0, 768, 0.0); // zero vector كـ fallback
        });
    }

    /**
     * توليد embeddings لـ دفعة من النصوص دفعة واحدة لتسريع العملية (40 ضعف أسرع)
     */
    private function getEmbeddingsBatch(array $texts): array
    {
        $uncachedIndices = [];
        $embeddings = array_fill(0, count($texts), null);

        // 1. بناء قائمة بمفاتيح الكاش ومطابقتها بالنصوص
        $cacheKeys = [];
        $keyToIdxMap = [];
        foreach ($texts as $i => $text) {
            $text = trim($text);
            $text = mb_substr($text, 0, 5000);
            
            // إذا كان النص فارغاً تماماً، نضع له متجه صفري مباشرة ولا نرسله لـ Gemini
            if ($text === '') {
                $embeddings[$i] = array_fill(0, 768, 0.0);
                continue;
            }

            $cacheKey = 'embed_' . md5($text);
            $cacheKeys[$i] = $cacheKey;
            $keyToIdxMap[$cacheKey] = $i;
        }

        // 2. جلب جميع مفاتيح الكاش بدفعة واحدة (Single SQL Query)
        try {
            $cachedValues = Cache::many(array_values($cacheKeys));
            foreach ($cachedValues as $key => $val) {
                if ($val !== null && is_array($val) && count($val) === 768) {
                    $idx = $keyToIdxMap[$key];
                    $embeddings[$idx] = $val;
                }
            }
        } catch (\Throwable $e) {
            Log::warning('[AzureSearch] Cache::many failed: ' . $e->getMessage());
        }

        // 3. بناء الطلبات للقيم غير الموجودة في الكاش
        $requests = [];
        $requestsMap = []; // لربط فهرس طلب الـ batch بالفهرس الأصلي
        foreach ($texts as $i => $text) {
            if ($embeddings[$i] === null) {
                $text = trim(mb_substr($text, 0, 5000));
                $requests[] = [
                    'model'   => "models/{$this->embeddingModel}",
                    'content' => ['parts' => [['text' => $text]]],
                    'taskType' => 'RETRIEVAL_DOCUMENT',
                    'outputDimensionality' => 768,
                ];
                $requestsMap[] = $i;
            }
        }

        if (empty($requests)) {
            return $embeddings;
        }

        // 4. طلب الـ embeddings من Gemini للقيم المتبقية (مع آلية إعادة المحاولة عند حدوث أخطاء مؤقتة)
        $maxRetries = 3;
        $retryDelay = 2; // ثوانٍ
        $response = null;

        for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
            try {
                $response = Http::withHeaders(['Content-Type' => 'application/json'])
                    ->timeout(30)
                    ->post(
                        "https://generativelanguage.googleapis.com/v1beta/models/{$this->embeddingModel}:batchEmbedContents?key={$this->geminiKey}",
                        ['requests' => $requests]
                    );

                if ($response->successful()) {
                    break; // نجاح الطلب، اخرج من حلقة المحاولات
                }

                Log::warning("[AzureSearch] Batch embedding API failed (Attempt {$attempt}/{$maxRetries})", [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);

            } catch (\Throwable $e) {
                Log::warning("[AzureSearch] Batch embedding API error (Attempt {$attempt}/{$maxRetries}): " . $e->getMessage());
            }

            if ($attempt < $maxRetries) {
                sleep($retryDelay);
                $retryDelay *= 2; // backoff مضاعف
            }
        }

        // 5. حفظ وحزم النتائج الناجحة
        if ($response && $response->successful()) {
            $results = $response->json('embeddings', []);
            foreach ($results as $idx => $result) {
                $originalIdx = $requestsMap[$idx];
                $vector = $result['values'] ?? [];
                if (!empty($vector)) {
                    $embeddings[$originalIdx] = $vector;
                    // حفظ في الكاش بشكل منفرد
                    $cacheKey = $cacheKeys[$originalIdx];
                    Cache::put($cacheKey, $vector, now()->addHours(24));
                }
            }
        }

        // 6. ملء أي قيم فشلت بـ zero vector كـ fallback
        foreach ($embeddings as $i => $vector) {
            if ($vector === null) {
                $embeddings[$i] = array_fill(0, 768, 0.0);
            }
        }

        return $embeddings;
    }

    /**
     * بناء body طلب Hybrid Search
     */
    private function buildHybridSearchBody(
        string $query,
        array $embedding,
        int $topK,
        ?string $filter
    ): array {
        return [
            'search'        => $query,
            'queryType'     => 'semantic',
            'semanticConfiguration' => 'legal-semantic-config',
            'queryLanguage' => 'ar-SA',
            'count'         => true,
            'top'           => $topK,
            'select'        => 'id,question,answer,domain,source_type,case_reference,law_system,relevance_score',
            'filter'        => $filter ?: null,
            'vectorQueries' => [
                [
                    'kind'       => 'vector',
                    'vector'     => $embedding,
                    'fields'     => 'embedding',
                    'k'          => $topK * 2,
                    'exhaustive' => false,
                ]
            ],
        ];
    }

    /**
     * تحويل نتائج Azure لـ Collection متوافقة مع LegalSearchService
     */
    private function parseSearchResults(array $response): Collection
    {
        $docs = $response['value'] ?? [];

        return collect($docs)->map(function ($doc) {
            return (object) [
                'id'              => $doc['id'] ?? null,
                'question'        => $doc['question'] ?? '',
                'correct_answer'  => $doc['answer'] ?? '',
                'case_text'       => $doc['answer'] ?? '',
                'source_type'     => $doc['source_type'] ?? 'unknown',
                'case_reference'  => $doc['case_reference'] ?? '',
                'law_system_name' => $doc['law_system'] ?? '',
                'relevance_score' => $doc['@search.score'] ?? 0,
                'reranker_score'  => $doc['@search.rerankerScore'] ?? null,
            ];
        });
    }

    /**
     * بناء OData filter string من array
     */
    private function buildFilter(array $filters): ?string
    {
        if (empty($filters)) {
            return null;
        }

        $parts = [];
        foreach ($filters as $field => $value) {
            $escaped = str_replace("'", "''", $value);
            $parts[] = "{$field} eq '{$escaped}'";
        }

        return implode(' and ', $parts);
    }

    /**
     * Fallback لـ LegalSearchService الحالي عند عدم توفر Azure
     */
    private function fallbackToKeywordSearch(string $query, int $topK): Collection
    {
        try {
            $service = app(LegalSearchService::class);
            return $service->search($query, $topK);
        } catch (\Throwable $e) {
            Log::error('[AzureSearch] Fallback also failed: ' . $e->getMessage());
            return collect();
        }
    }

    /**
     * التحقق من وجود بيانات الاتصال
     */
    private function isConfigured(): bool
    {
        return ! empty($this->endpoint) && ! empty($this->apiKey);
    }

    /**
     * Schema الـ index القانوني في Azure AI Search
     */
    private function getLegalIndexSchema(): array
    {
        return [
            'name' => $this->indexName,
            'fields' => [
                [
                    'name'       => 'id',
                    'type'       => 'Edm.String',
                    'key'        => true,
                    'filterable' => true,
                ],
                [
                    'name'       => 'question',
                    'type'       => 'Edm.String',
                    'searchable' => true,
                    'analyzer'   => 'ar.microsoft',
                ],
                [
                    'name'       => 'answer',
                    'type'       => 'Edm.String',
                    'searchable' => true,
                    'analyzer'   => 'ar.microsoft',
                ],
                [
                    'name'       => 'case_text',
                    'type'       => 'Edm.String',
                    'searchable' => true,
                    'analyzer'   => 'ar.microsoft',
                ],
                [
                    'name'        => 'domain',
                    'type'        => 'Edm.String',
                    'filterable'  => true,
                    'facetable'   => true,
                ],
                [
                    'name'       => 'source_type',
                    'type'       => 'Edm.String',
                    'filterable' => true,
                    'facetable'  => true,
                ],
                [
                    'name'       => 'law_system',
                    'type'       => 'Edm.String',
                    'filterable' => true,
                    'searchable' => true,
                    'analyzer'   => 'ar.microsoft',
                ],
                [
                    'name'       => 'case_reference',
                    'type'       => 'Edm.String',
                    'searchable' => true,
                ],
                [
                    'name'         => 'embedding',
                    'type'         => 'Collection(Edm.Single)',
                    'searchable'   => true,
                    'dimensions'   => 768, // Gemini text-embedding-004
                    'vectorSearchProfile' => 'radiif-vector-profile',
                ],
            ],
            'vectorSearch' => [
                'profiles' => [
                    [
                        'name'      => 'radiif-vector-profile',
                        'algorithm' => 'radiif-hnsw',
                    ]
                ],
                'algorithms' => [
                    [
                        'name' => 'radiif-hnsw',
                        'kind' => 'hnsw',
                        'hnswParameters' => [
                            'metric'         => 'cosine',
                            'm'              => 4,
                            'efConstruction' => 400,
                            'efSearch'       => 500,
                        ]
                    ]
                ],
            ],
            'semantic' => [
                'configurations' => [
                    [
                        'name' => 'legal-semantic-config',
                        'prioritizedFields' => [
                            'titleField'                 => ['fieldName' => 'question'],
                            'prioritizedContentFields'   => [['fieldName' => 'answer'], ['fieldName' => 'case_text']],
                            'prioritizedKeywordsFields'  => [['fieldName' => 'law_system'], ['fieldName' => 'domain']],
                        ]
                    ]
                ]
            ],
        ];
    }
}
