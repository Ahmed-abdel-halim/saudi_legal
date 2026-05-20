<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Azure Blob Storage Service
 * ─────────────────────────────────────────────────────────────────────────────
 * يدير رفع وتنزيل الملفات من Azure Blob Storage
 * يستخدم REST API مباشرة (بدون package إضافي)
 * ─────────────────────────────────────────────────────────────────────────────
 */
class AzureBlobService
{
    protected string $accountName;
    protected string $accountKey;
    protected string $baseUrl;

    public function __construct()
    {
        $this->accountName = config('azure.storage.name', '');
        $this->accountKey  = config('azure.storage.key', '');
        $this->baseUrl     = config('azure.storage.url', '');
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  PUBLIC API
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * رفع ملف (UploadedFile من Laravel)
     *
     * @param  UploadedFile  $file
     * @param  string        $container   e.g. 'user-uploads'
     * @param  string|null   $folder      e.g. 'avatars', 'contracts'
     * @return string|null   URL كامل للملف أو null عند الفشل
     */
    public function upload(UploadedFile $file, string $container, ?string $folder = null): ?string
    {
        $extension = $file->getClientOriginalExtension();
        $blobName  = ($folder ? rtrim($folder, '/') . '/' : '') . Str::uuid() . '.' . $extension;

        return $this->uploadContent(
            $file->get(),
            $container,
            $blobName,
            $file->getMimeType()
        );
    }

    /**
     * رفع محتوى نصي (JSON, JSONL, CSV, إلخ)
     *
     * @param  string  $content    المحتوى
     * @param  string  $container  اسم الـ container
     * @param  string  $blobName   المسار داخل الـ container
     * @param  string  $mimeType   نوع المحتوى
     * @return string|null URL أو null عند الفشل
     */
    public function uploadContent(
        string $content,
        string $container,
        string $blobName,
        string $mimeType = 'application/octet-stream'
    ): ?string {
        if (! $this->isConfigured()) {
            Log::warning('[AzureBlob] Not configured — skipping upload.');
            return null;
        }

        $url     = "{$this->baseUrl}/{$container}/{$blobName}";
        $date    = gmdate('D, d M Y H:i:s T');
        $length  = strlen($content);
        $headers = $this->buildHeaders($content, $container, $blobName, $mimeType, $date, $length);

        try {
            $response = Http::withHeaders($headers)->withBody($content, $mimeType)->put($url);

            if ($response->successful()) {
                Log::info("[AzureBlob] ✅ Uploaded: {$url}");
                return $url;
            }

            Log::error('[AzureBlob] Upload failed', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
            return null;

        } catch (\Throwable $e) {
            Log::error('[AzureBlob] Exception: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * تنزيل محتوى blob
     */
    public function download(string $container, string $blobName): ?string
    {
        if (! $this->isConfigured()) return null;

        $url  = "{$this->baseUrl}/{$container}/{$blobName}";
        $date = gmdate('D, d M Y H:i:s T');

        $stringToSign = "GET\n\n\n\n\n\n\n\n\n\n\n\nx-ms-date:{$date}\nx-ms-version:2020-10-02\n/{$this->accountName}/{$container}/{$blobName}";
        $signature    = base64_encode(hash_hmac('sha256', $stringToSign, base64_decode($this->accountKey), true));

        try {
            $response = Http::withHeaders([
                'x-ms-date'    => $date,
                'x-ms-version' => '2020-10-02',
                'Authorization' => "SharedKey {$this->accountName}:{$signature}",
            ])->get($url);

            return $response->successful() ? $response->body() : null;

        } catch (\Throwable $e) {
            Log::error('[AzureBlob] Download Exception: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * حذف blob
     */
    public function delete(string $container, string $blobName): bool
    {
        if (! $this->isConfigured()) return false;

        $url  = "{$this->baseUrl}/{$container}/{$blobName}";
        $date = gmdate('D, d M Y H:i:s T');

        $stringToSign = "DELETE\n\n\n\n\n\n\n\n\n\n\n\nx-ms-date:{$date}\nx-ms-version:2020-10-02\n/{$this->accountName}/{$container}/{$blobName}";
        $signature    = base64_encode(hash_hmac('sha256', $stringToSign, base64_decode($this->accountKey), true));

        try {
            $response = Http::withHeaders([
                'x-ms-date'     => $date,
                'x-ms-version'  => '2020-10-02',
                'Authorization' => "SharedKey {$this->accountName}:{$signature}",
            ])->delete($url);

            return $response->successful();

        } catch (\Throwable $e) {
            Log::error('[AzureBlob] Delete Exception: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * قائمة الـ blobs في container
     */
    public function listBlobs(string $container, string $prefix = ''): array
    {
        if (! $this->isConfigured()) return [];

        $url  = "{$this->baseUrl}/{$container}?restype=container&comp=list&prefix={$prefix}";
        $date = gmdate('D, d M Y H:i:s T');

        $stringToSign = "GET\n\n\n\n\n\n\n\n\n\n\n\nx-ms-date:{$date}\nx-ms-version:2020-10-02\n/{$this->accountName}/{$container}\ncomp:list\nprefix:{$prefix}\nrestype:container";
        $signature    = base64_encode(hash_hmac('sha256', $stringToSign, base64_decode($this->accountKey), true));

        try {
            $response = Http::withHeaders([
                'x-ms-date'     => $date,
                'x-ms-version'  => '2020-10-02',
                'Authorization' => "SharedKey {$this->accountName}:{$signature}",
            ])->get($url);

            if (! $response->successful()) return [];

            // Parse XML response
            $xml   = simplexml_load_string($response->body());
            $blobs = [];

            foreach ($xml->Blobs->Blob ?? [] as $blob) {
                $blobs[] = [
                    'name'          => (string) $blob->Name,
                    'size'          => (int) $blob->Properties->{'Content-Length'},
                    'content_type'  => (string) $blob->Properties->{'Content-Type'},
                    'last_modified' => (string) $blob->Properties->{'Last-Modified'},
                    'url'           => "{$this->baseUrl}/{$container}/{$blob->Name}",
                ];
            }

            return $blobs;

        } catch (\Throwable $e) {
            Log::error('[AzureBlob] ListBlobs Exception: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * رفع ملف JSONL الضخم بشكل chunked
     */
    public function uploadLargeFile(string $filePath, string $container, string $blobName): ?string
    {
        if (! $this->isConfigured() || ! file_exists($filePath)) return null;

        $content = file_get_contents($filePath);
        $mimeType = 'application/jsonlines+json';

        return $this->uploadContent($content, $container, $blobName, $mimeType);
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  PRIVATE HELPERS
    // ─────────────────────────────────────────────────────────────────────────

    private function buildHeaders(
        string $content,
        string $container,
        string $blobName,
        string $mimeType,
        string $date,
        int    $length
    ): array {
        $contentMd5   = base64_encode(md5($content, true));
        $canonicalHeaders = "x-ms-blob-type:BlockBlob\nx-ms-date:{$date}\nx-ms-version:2020-10-02";
        $canonicalResource = "/{$this->accountName}/{$container}/{$blobName}";

        $stringToSign = implode("\n", [
            'PUT',           // HTTP Method
            '',              // Content-Encoding
            '',              // Content-Language
            $length,         // Content-Length
            $contentMd5,     // Content-MD5
            $mimeType,       // Content-Type
            '',              // Date
            '',              // If-Modified-Since
            '',              // If-Match
            '',              // If-None-Match
            '',              // If-Unmodified-Since
            '',              // Range
            $canonicalHeaders,
            $canonicalResource,
        ]);

        $signature = base64_encode(
            hash_hmac('sha256', $stringToSign, base64_decode($this->accountKey), true)
        );

        return [
            'x-ms-blob-type' => 'BlockBlob',
            'x-ms-date'      => $date,
            'x-ms-version'   => '2020-10-02',
            'Content-Type'   => $mimeType,
            'Content-Length' => $length,
            'Content-MD5'    => $contentMd5,
            'Authorization'  => "SharedKey {$this->accountName}:{$signature}",
        ];
    }

    private function isConfigured(): bool
    {
        return ! empty($this->accountName) && ! empty($this->accountKey);
    }
}
