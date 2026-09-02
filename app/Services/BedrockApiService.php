<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BedrockApiService
{
    protected string $accessKey;
    protected string $secretKey;
    protected string $region;
    protected string $modelId;
    protected bool $enabled;

    public function __construct()
    {
        $this->accessKey = trim(config('services.bedrock.key', env('AWS_BEDROCK_ACCESS_KEY_ID', env('AWS_ACCESS_KEY_ID', ''))));
        $this->secretKey = trim(config('services.bedrock.secret', env('AWS_BEDROCK_SECRET_ACCESS_KEY', env('AWS_SECRET_ACCESS_KEY', ''))));
        $this->region    = trim(config('services.bedrock.region', env('AWS_BEDROCK_REGION', env('AWS_DEFAULT_REGION', 'us-east-1'))));
        $this->modelId   = trim(config('services.bedrock.model', env('AWS_BEDROCK_MODEL', 'us.anthropic.claude-sonnet-5')));
        $this->enabled   = (bool) config('services.bedrock.enabled', env('BEDROCK_ENABLED', false));
    }

    public function isEnabled(): bool
    {
        return $this->enabled && !empty($this->accessKey) && !empty($this->secretKey);
    }

    /**
     * استدعاء Amazon Bedrock (Anthropic Claude 3.5 Sonnet / Haiku)
     *
     * @param array $messages  [['role' => 'user'|'assistant', 'content' => '...']]
     * @param string|null $systemPrompt
     * @param array $options   ['temperature' => 0.1, 'max_tokens' => 4096, 'timeout' => 45]
     * @return string|null
     */
    public function generateContent(array $messages, ?string $systemPrompt = null, array $options = []): ?string
    {
        if (!$this->isEnabled()) {
            return null;
        }

        $temperature = $options['temperature'] ?? 0.1;
        $maxTokens   = $options['max_tokens'] ?? 4096;
        $timeout     = $options['timeout'] ?? 60;
        $useCache    = $options['cache'] ?? true;
        $cacheTtl    = $options['cache_ttl'] ?? 3600; // ساعة واحدة افتراضياً

        // Cache لتوفير الكوتا — نفس السؤال لا يُرسل مرتين
        if ($useCache) {
            $cacheKey = 'bedrock:' . md5(json_encode($messages) . $systemPrompt . $temperature);
            $cached = Cache::get($cacheKey);
            if ($cached !== null) {
                return $cached;
            }
        }

        $candidateModels = array_unique(array_filter([
            $options['model'] ?? null,
            $this->modelId,
            // Claude Sonnet 5 - Primary (مشترك من Marketplace)
            'us.anthropic.claude-sonnet-5',
            // Claude Sonnet 4.6 - Fallback (مشترك من Marketplace - مؤكد شغّال)
            'us.anthropic.claude-sonnet-4-6',
        ]));

        // تجهيز الـ Payload وفق صيغة Anthropic Messages API المدعومة في Bedrock
        $body = [
            'anthropic_version' => 'bedrock-2023-05-31',
            'max_tokens'        => $maxTokens,
            'temperature'       => $temperature,
            'messages'          => $messages,
        ];

        if (!empty($systemPrompt)) {
            $body['system'] = $systemPrompt;
        }

        $payloadJson = json_encode($body, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $quotaExceeded = false;

        foreach ($candidateModels as $modelId) {
            $endpoint = "https://bedrock-runtime.{$this->region}.amazonaws.com/model/" . rawurlencode($modelId) . "/invoke";

            try {
                $headers = $this->signRequest(
                    'POST',
                    $endpoint,
                    $payloadJson,
                    'bedrock',
                    $this->region,
                    $this->accessKey,
                    $this->secretKey
                );

                $response = Http::withHeaders($headers)
                    ->timeout($timeout)
                    ->withBody($payloadJson, 'application/json')
                    ->post($endpoint);

                if ($response->successful()) {
                    $data = $response->json();
                    $content = $data['content'] ?? [];
                    $textParts = [];

                    foreach ($content as $item) {
                        if (($item['type'] ?? '') === 'text') {
                            $textParts[] = $item['text'] ?? '';
                        }
                    }

                    $text = trim(implode('', $textParts));
                    if (!empty($text)) {
                        // احفظ في الـ cache لتوفير الكوتا
                        if ($useCache) {
                            Cache::put($cacheKey, $text, $cacheTtl);
                        }
                        return $text;
                    }
                }

                $status = $response->status();
                $errorMsg = json_decode($response->body(), true)['message'] ?? $response->body();

                // لو الكوتا خلصت، مفيش فايدة نجرب باقي الموديلات
                if ($status === 429 && str_contains($errorMsg, 'tokens per day')) {
                    Log::warning("[BedrockApiService] Daily quota exceeded for model {$modelId}. Stopping fallback.");
                    $quotaExceeded = true;
                    break;
                }

                Log::warning("[BedrockApiService] Model {$modelId} failed ({$status}): " . mb_substr($response->body(), 0, 300));
            } catch (\Throwable $e) {
                Log::warning("[BedrockApiService] Exception calling Bedrock model {$modelId}: " . $e->getMessage());
            }
        }

        if ($quotaExceeded) {
            Log::error('[BedrockApiService] All models failed: Daily token quota exceeded. Request quota increase from AWS Service Quotas.');
        }

        return null;
    }

    /**
     * حساب وتوقيع ترويسات AWS SigV4
     */
    protected function signRequest(
        string $method,
        string $url,
        string $body,
        string $service,
        string $region,
        string $accessKey,
        string $secretKey
    ): array {
        $parsedUrl = parse_url($url);
        $host      = $parsedUrl['host'];
        $path      = $parsedUrl['path'] ?? '/';
        $canonicalUri = implode('/', array_map('rawurlencode', explode('/', $path)));

        $timestamp = gmdate('Ymd\THis\Z');
        $dateStamp = gmdate('Ymd');

        $headers = [
            'content-type' => 'application/json',
            'host'         => $host,
            'x-amz-date'   => $timestamp,
        ];

        ksort($headers);
        $canonicalHeaders = '';
        $signedHeadersArr = [];
        foreach ($headers as $k => $v) {
            $canonicalHeaders .= strtolower($k) . ':' . trim($v) . "\n";
            $signedHeadersArr[] = strtolower($k);
        }
        $signedHeadersStr = implode(';', $signedHeadersArr);

        $payloadHash = hash('sha256', $body);
        $canonicalRequest = "{$method}\n{$canonicalUri}\n\n{$canonicalHeaders}\n{$signedHeadersStr}\n{$payloadHash}";

        $algorithm = 'AWS4-HMAC-SHA256';
        $credentialScope = "{$dateStamp}/{$region}/{$service}/aws4_request";
        $stringToSign = "{$algorithm}\n{$timestamp}\n{$credentialScope}\n" . hash('sha256', $canonicalRequest);

        // حساب مفتاح التوقيع (Signing Key)
        $kSecret  = 'AWS4' . $secretKey;
        $kDate    = hash_hmac('sha256', $dateStamp, $kSecret, true);
        $kRegion  = hash_hmac('sha256', $region, $kDate, true);
        $kService = hash_hmac('sha256', $service, $kRegion, true);
        $kSigning = hash_hmac('sha256', 'aws4_request', $kService, true);

        $signature = hash_hmac('sha256', $stringToSign, $kSigning);

        $authorization = "{$algorithm} Credential={$accessKey}/{$credentialScope}, SignedHeaders={$signedHeadersStr}, Signature={$signature}";

        return [
            'Content-Type'  => 'application/json',
            'Host'          => $host,
            'x-amz-date'    => $timestamp,
            'Authorization' => $authorization,
        ];
    }
}
