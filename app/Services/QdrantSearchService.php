<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

/**
 * Qdrant Vector Search Service
 * ─────────────────────────────────────────────────────────────────────────────
 * يدير البحث الذكي بالمعنى (Semantic Search) عبر Qdrant Cloud
 * يستخدم Gemini text-embedding-004 لتوليد الـ Vectors
 * ─────────────────────────────────────────────────────────────────────────────
 *
 * Architecture:
 *   User Question → Gemini Embedding → Qdrant Cosine Search → Top-K Results
 *
 * @see https://qdrant.tech/documentation/
 */
class QdrantSearchService
{
    protected string $qdrantUrl;
    protected string $qdrantApiKey;
    protected string $collectionName;

    // Gemini embedding config
    protected string $geminiKey;
    protected string $embeddingModel = 'gemini-embedding-2';
    protected int $embeddingDimensions = 768;

    // Rate limiting: Gemini free tier = 1500 requests/minute
    protected int $embeddingBatchDelay = 100; // ms between batches

    public function __construct()
    {
        $this->qdrantUrl      = rtrim(config('services.qdrant.url', ''), '/');
        $this->qdrantApiKey   = config('services.qdrant.api_key', '');
        $this->collectionName = config('services.qdrant.collection', 'legal-documents');
        $this->geminiKey      = config('services.gemini.key', env('GEMINI_API_KEY'));
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  PUBLIC API — Search
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * بحث بالمعنى الدلالي (Semantic Search)
     *
     * يحول السؤال إلى vector ويبحث عن أقرب النتائج في Qdrant.
     * النتائج مرتبة حسب التشابه (Cosine Similarity).
     *
     * @param  string  $query    السؤال القانوني
     * @param  int     $topK     عدد النتائج المطلوبة
     * @param  array   $filters  فلاتر اختيارية ['domain' => 'law', 'source_type' => 'judgment']
     * @return Collection        نتائج البحث مرتبة حسب التشابه
     */
    public function search(string $query, int $topK = 5, array $filters = []): Collection
    {
        if (! $this->isConfigured()) {
            Log::warning('[Qdrant] Not configured — skipping vector search.');
            return collect();
        }

        try {
            $embedding = $this->getEmbedding($query);

            if (empty($embedding) || count($embedding) !== $this->embeddingDimensions) {
                Log::warning('[Qdrant] Invalid embedding generated for query.');
                return collect();
            }

            $body = [
                'vector' => $embedding,
                'limit'  => $topK,
                'with_payload' => true,
                'score_threshold' => 0.45, // Minimum similarity — أقل من كده مش relevant
            ];

            // إضافة فلاتر لو موجودة
            if (! empty($filters)) {
                $body['filter'] = $this->buildFilter($filters);
            }

            $response = Http::withHeaders([
                'api-key'      => $this->qdrantApiKey,
                'Content-Type' => 'application/json',
            ])->timeout(15)->post(
                "{$this->qdrantUrl}/collections/{$this->collectionName}/points/search",
                $body
            );

            if (! $response->successful()) {
                Log::error('[Qdrant] Search failed', [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);
                return collect();
            }

            return $this->parseSearchResults($response->json('result', []));

        } catch (\Throwable $e) {
            Log::error('[Qdrant] Search Exception: ' . $e->getMessage());
            return collect();
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  PUBLIC API — Indexing
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * إنشاء الـ Collection في Qdrant (تُشغّل مرة واحدة)
     *
     * تنشئ collection بالمواصفات:
     *   - 768 dimensions (Gemini text-embedding-004)
     *   - Cosine distance metric
     *   - HNSW index مُحسّن للبحث السريع
     */
    public function createCollection(): bool
    {
        if (! $this->isConfigured()) {
            return false;
        }

        try {
            // تحقق هل الـ collection موجود فعلاً
            $check = Http::withHeaders(['api-key' => $this->qdrantApiKey])
                ->get("{$this->qdrantUrl}/collections/{$this->collectionName}");

            if ($check->successful()) {
                Log::info("[Qdrant] Collection '{$this->collectionName}' already exists.");
                return true;
            }

            // إنشاء collection جديد
            $response = Http::withHeaders([
                'api-key'      => $this->qdrantApiKey,
                'Content-Type' => 'application/json',
            ])->put(
                "{$this->qdrantUrl}/collections/{$this->collectionName}",
                [
                    'vectors' => [
                        'size'     => $this->embeddingDimensions,
                        'distance' => 'Cosine',
                    ],
                    // Optimizations for 50K+ documents
                    'optimizers_config' => [
                        'indexing_threshold' => 20000,
                    ],
                    'hnsw_config' => [
                        'm'                => 16,
                        'ef_construct'     => 100,
                        'full_scan_threshold' => 10000,
                    ],
                ]
            );

            if ($response->successful()) {
                Log::info("[Qdrant] Collection '{$this->collectionName}' created successfully.");

                // إنشاء payload indexes للفلترة السريعة
                $this->createPayloadIndexes();

                return true;
            }

            Log::error('[Qdrant] Create collection failed', ['body' => $response->body()]);
            return false;

        } catch (\Throwable $e) {
            Log::error('[Qdrant] CreateCollection Exception: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * رفع دفعة من الوثائق مع توليد embeddings
     *
     * كل وثيقة يجب أن تحتوي على:
     *   - id: معرف فريد (string أو int)
     *   - question: السؤال
     *   - answer: الإجابة
     *   - باقي الحقول اختيارية (domain, source_type, case_reference, ...)
     *
     * @param  array  $documents  مصفوفة الوثائق
     * @return array  ['success' => int, 'failed' => int]
     */
    public function upsertBatch(array $documents): array
    {
        if (! $this->isConfigured()) {
            return ['success' => 0, 'failed' => count($documents)];
        }

        $points = [];
        $failed = 0;

        // 1. استخراج النصوص للـ batch embedding
        $textsToEmbed = [];
        $validDocuments = [];

        foreach ($documents as $doc) {
            $textsToEmbed[] = $this->buildEmbeddingText($doc);
            $validDocuments[] = $doc;
        }

        // 2. توليد الـ embeddings دفعة واحدة باستخدام Gemini API Batching
        $embeddings = $this->getEmbeddingsBatch($textsToEmbed);

        // 3. بناء النقاط (points) لـ Qdrant
        foreach ($validDocuments as $index => $doc) {
            $embedding = $embeddings[$index] ?? [];

            if (empty($embedding) || count($embedding) !== $this->embeddingDimensions || $this->isZeroVector($embedding)) {
                $failed++;
                continue;
            }

            try {
                $points[] = [
                    'id'      => $this->generatePointId($doc['id']),
                    'vector'  => $embedding,
                    'payload' => [
                        'question'       => mb_substr($doc['question'] ?? '', 0, 5000),
                        'answer'         => mb_substr($doc['answer'] ?? '', 0, 10000),
                        'domain'         => $doc['domain'] ?? 'law',
                        'source_type'    => $doc['source_type'] ?? 'judgment',
                        'law_system'     => $doc['law_system'] ?? '',
                        'case_reference' => $doc['case_reference'] ?? '',
                        'is_verified'    => $doc['is_verified'] ?? false,
                        'original_id'    => $doc['id'] ?? '',
                    ],
                ];
            } catch (\Throwable $e) {
                $failed++;
                Log::warning('[Qdrant] Failed to build point payload', [
                    'id'    => $doc['id'] ?? 'unknown',
                    'error' => $e->getMessage(),
                ]);
            }
        }

        if (empty($points)) {
            return ['success' => 0, 'failed' => $failed];
        }

        // رفع الـ batch لـ Qdrant
        try {
            $response = Http::withHeaders([
                'api-key'      => $this->qdrantApiKey,
                'Content-Type' => 'application/json',
            ])->timeout(60)->put(
                "{$this->qdrantUrl}/collections/{$this->collectionName}/points",
                ['points' => $points]
            );

            if ($response->successful()) {
                return ['success' => count($points), 'failed' => $failed];
            }

            Log::error('[Qdrant] Upsert batch failed', [
                'status' => $response->status(),
                'body'   => mb_substr($response->body(), 0, 500),
            ]);
            return ['success' => 0, 'failed' => $failed + count($points)];

        } catch (\Throwable $e) {
            Log::error('[Qdrant] Upsert Exception: ' . $e->getMessage());
            return ['success' => 0, 'failed' => $failed + count($points)];
        }
    }

    /**
     * حذف كل البيانات من الـ Collection (إعادة تعيين)
     */
    public function deleteAllPoints(): bool
    {
        if (! $this->isConfigured()) {
            return false;
        }

        try {
            // حذف الـ collection بالكامل وإعادة إنشائه
            Http::withHeaders(['api-key' => $this->qdrantApiKey])
                ->delete("{$this->qdrantUrl}/collections/{$this->collectionName}");

            return $this->createCollection();

        } catch (\Throwable $e) {
            Log::error('[Qdrant] DeleteAll Exception: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * إحصائيات الـ Collection
     */
    public function getCollectionInfo(): ?array
    {
        if (! $this->isConfigured()) {
            return null;
        }

        try {
            $response = Http::withHeaders(['api-key' => $this->qdrantApiKey])
                ->get("{$this->qdrantUrl}/collections/{$this->collectionName}");

            if ($response->successful()) {
                $result = $response->json('result', []);
                return [
                    'status'       => $result['status'] ?? 'unknown',
                    'points_count' => $result['points_count'] ?? 0,
                    'vectors_count'=> $result['vectors_count'] ?? 0,
                    'segments'     => count($result['segments'] ?? []),
                    'config'       => $result['config'] ?? [],
                ];
            }

            return null;

        } catch (\Throwable $e) {
            return null;
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  PUBLIC API — Status
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * هل الخدمة مُعدة وجاهزة؟
     */
    public function isConfigured(): bool
    {
        return ! empty($this->qdrantUrl) && ! empty($this->qdrantApiKey);
    }

    /**
     * هل الخدمة مفعّلة في الإعدادات؟
     */
    public function isEnabled(): bool
    {
        return $this->isConfigured() && config('services.qdrant.enabled', false);
    }

    /**
     * فحص الاتصال بـ Qdrant
     */
    public function healthCheck(): array
    {
        if (! $this->isConfigured()) {
            return [
                'status'  => 'not_configured',
                'message' => 'Qdrant URL or API Key is missing from .env',
            ];
        }

        try {
            $response = Http::withHeaders(['api-key' => $this->qdrantApiKey])
                ->timeout(5)
                ->get("{$this->qdrantUrl}/collections");

            if ($response->successful()) {
                $collections = $response->json('result.collections', []);
                $hasCollection = collect($collections)->contains('name', $this->collectionName);

                return [
                    'status'          => 'healthy',
                    'qdrant_version'  => $response->json('result.version', 'unknown'),
                    'collection_exists' => $hasCollection,
                    'collection_name' => $this->collectionName,
                ];
            }

            return [
                'status'  => 'error',
                'message' => 'Qdrant responded with status ' . $response->status(),
            ];

        } catch (\Throwable $e) {
            return [
                'status'  => 'unreachable',
                'message' => $e->getMessage(),
            ];
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  PRIVATE HELPERS
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * توليد embedding عبر Gemini API مع cache ذكي
     *
     * Cache strategy:
     *   - كل embedding يتخزن 7 أيام (كافي للـ indexing مرة واحدة)
     *   - الـ search queries تتخزن 24 ساعة
     */
    public function getEmbedding(string $text): array
    {
        // تقليم النص لحد Gemini (max ~2000 tokens ≈ 5000 chars عربي)
        $text = trim($text);
        if (empty($text)) {
            return array_fill(0, $this->embeddingDimensions, 0.0);
        }

        $text = mb_substr($text, 0, 5000);
        $cacheKey = 'qdrant_embed_' . md5($text);

        $cached = Cache::get($cacheKey);
        if ($cached !== null && count($cached) === $this->embeddingDimensions && !$this->isZeroVector($cached)) {
            return $cached;
        }

        try {
            $response = Http::withHeaders(['Content-Type' => 'application/json'])
                ->timeout(15)
                ->post(
                    "https://generativelanguage.googleapis.com/v1beta/models/{$this->embeddingModel}:embedContent?key={$this->geminiKey}",
                    [
                        'model'    => "models/{$this->embeddingModel}",
                        'content'  => ['parts' => [['text' => $text]]],
                        'taskType' => 'RETRIEVAL_DOCUMENT',
                        'outputDimensionality' => $this->embeddingDimensions,
                    ]
                );

            if ($response->successful()) {
                $values = $response->json('embedding.values', []);
                if (count($values) === $this->embeddingDimensions && !$this->isZeroVector($values)) {
                    Cache::put($cacheKey, $values, now()->addDays(7));
                    return $values;
                }
            }

            Log::warning('[Qdrant] Gemini Embedding failed', [
                'status' => $response->status(),
                'body'   => mb_substr($response->body(), 0, 200),
            ]);

        } catch (\Throwable $e) {
            Log::error('[Qdrant] Embedding Exception: ' . $e->getMessage());
        }

        return array_fill(0, $this->embeddingDimensions, 0.0);
    }

    /**
     * بناء نص الـ embedding من الوثيقة
     *
     * Strategy: السؤال أهم من الإجابة، والإجابة أهم من نص القضية.
     * لذلك نضع السؤال أولاً ونقصر نص القضية.
     */
    private function buildEmbeddingText(array $doc): string
    {
        $parts = [];

        if (! empty($doc['question'])) {
            $parts[] = $doc['question'];
        }

        if (! empty($doc['answer'])) {
            // قصر الإجابة لـ 2000 حرف
            $parts[] = mb_substr($doc['answer'], 0, 2000);
        }

        if (! empty($doc['case_text'])) {
            // قصر نص القضية لـ 1000 حرف
            $parts[] = mb_substr($doc['case_text'], 0, 1000);
        }

        return implode("\n", $parts);
    }

    /**
     * تحويل الـ string ID لـ unsigned integer (Qdrant يحتاج int أو UUID)
     */
    private function generatePointId(string $id): int
    {
        // CRC32 ينتج unsigned int — مناسب لـ Qdrant point ID
        // نضيف prefix hash لتجنب التصادم بين الأنواع المختلفة
        return abs(crc32($id));
    }

    /**
     * تحويل نتائج Qdrant لـ Collection متوافقة مع LegalSearchService
     */
    private function parseSearchResults(array $results): Collection
    {
        return collect($results)->map(function ($point) {
            $payload = $point['payload'] ?? [];

            return (object) [
                'id'              => $payload['original_id'] ?? $point['id'],
                'question'        => $payload['question'] ?? '',
                'correct_answer'  => $payload['answer'] ?? '',
                'case_text'       => $payload['answer'] ?? '',
                'source_type'     => $payload['source_type'] ?? 'judgment',
                'case_reference'  => $payload['case_reference'] ?? '',
                'law_system_name' => $payload['law_system'] ?? '',
                'relevance_score' => round(($point['score'] ?? 0) * 100, 2),
                'is_verified'     => $payload['is_verified'] ?? false,
                'search_method'   => 'vector',
            ];
        });
    }

    /**
     * بناء Qdrant filter من array
     */
    private function buildFilter(array $filters): array
    {
        $must = [];

        foreach ($filters as $field => $value) {
            $must[] = [
                'key'   => $field,
                'match' => ['value' => $value],
            ];
        }

        return ['must' => $must];
    }

    /**
     * إنشاء payload indexes للفلترة السريعة
     */
    private function createPayloadIndexes(): void
    {
        $fieldsToIndex = [
            ['field_name' => 'domain',      'field_schema' => 'keyword'],
            ['field_name' => 'source_type', 'field_schema' => 'keyword'],
            ['field_name' => 'is_verified', 'field_schema' => 'bool'],
            ['field_name' => 'law_system',  'field_schema' => 'keyword'],
        ];

        foreach ($fieldsToIndex as $field) {
            try {
                Http::withHeaders([
                    'api-key'      => $this->qdrantApiKey,
                    'Content-Type' => 'application/json',
                ])->put(
                    "{$this->qdrantUrl}/collections/{$this->collectionName}/index",
                    $field
                );
            } catch (\Throwable $e) {
                Log::warning("[Qdrant] Failed to create index for {$field['field_name']}: " . $e->getMessage());
            }
        }
    }

    /**
     * توليد embeddings لمجموعة نصوص دفعة واحدة لتسريع العملية (Batch Embedding)
     *
     * @param  array  $texts  مصفوفة النصوص
     * @return array         مصفوفة من الـ vectors بنفس الترتيب
     */
    public function getEmbeddingsBatch(array $texts): array
    {
        if (empty($texts)) {
            return [];
        }

        $cleanTexts = [];
        $cachedEmbeddings = [];
        $uncachedIndexes = [];
        $uncachedTexts = [];

        foreach ($texts as $index => $text) {
            $trimmed = trim($text);
            if (empty($trimmed)) {
                $cachedEmbeddings[$index] = array_fill(0, $this->embeddingDimensions, 0.0);
                continue;
            }
            $cleanText = mb_substr($trimmed, 0, 5000);
            $cleanTexts[$index] = $cleanText;
            $cacheKey = 'qdrant_embed_' . md5($cleanText);

            $cached = Cache::get($cacheKey);
            if ($cached !== null && count($cached) === $this->embeddingDimensions) {
                $cachedEmbeddings[$index] = $cached;
            } else {
                $uncachedIndexes[] = $index;
                $uncachedTexts[] = $cleanText;
            }
        }

        if (empty($uncachedTexts)) {
            return $cachedEmbeddings;
        }

        // Gemini batch API allows up to 100 requests per batch call, using 50 to prevent timeouts
        $chunks = array_chunk($uncachedTexts, 50);
        $chunkIndexes = array_chunk($uncachedIndexes, 50);

        foreach ($chunks as $chunkIdx => $chunkTexts) {
            $indexes = $chunkIndexes[$chunkIdx];
            $requests = [];
            foreach ($chunkTexts as $text) {
                $requests[] = [
                    'model' => "models/{$this->embeddingModel}",
                    'content' => ['parts' => [['text' => $text]]],
                    'taskType' => 'RETRIEVAL_DOCUMENT',
                    'outputDimensionality' => $this->embeddingDimensions,
                ];
            }

            try {
                $response = Http::withHeaders(['Content-Type' => 'application/json'])
                    ->timeout(60)
                    ->post(
                        "https://generativelanguage.googleapis.com/v1beta/models/{$this->embeddingModel}:batchEmbedContents?key={$this->geminiKey}",
                        ['requests' => $requests]
                    );

                if ($response->successful()) {
                    $embeddingsResult = $response->json('embeddings', []);
                    foreach ($embeddingsResult as $i => $emb) {
                        $values = $emb['values'] ?? [];
                        if (count($values) === $this->embeddingDimensions && !$this->isZeroVector($values)) {
                            $targetIndex = $indexes[$i];
                            $cachedEmbeddings[$targetIndex] = $values;
                            
                            $cacheKey = 'qdrant_embed_' . md5($chunkTexts[$i]);
                            Cache::put($cacheKey, $values, now()->addDays(7));
                        }
                    }
                } else {
                    Log::error('[Qdrant] Batch embedding failed', [
                        'status' => $response->status(),
                        'body'   => mb_substr($response->body(), 0, 500),
                    ]);
                }
            } catch (\Throwable $e) {
                Log::error('[Qdrant] Batch embedding exception: ' . $e->getMessage());
            }

            // Small delay to protect rate limits
            usleep(200000); // 200ms
        }

        foreach ($texts as $index => $text) {
            if (!isset($cachedEmbeddings[$index])) {
                $cachedEmbeddings[$index] = array_fill(0, $this->embeddingDimensions, 0.0);
            }
        }

        ksort($cachedEmbeddings);
        return $cachedEmbeddings;
    }

    private function isZeroVector(array $vector): bool
    {
        foreach ($vector as $val) {
            if ($val !== 0.0 && $val !== 0) {
                return false;
            }
        }
        return true;
    }
}
