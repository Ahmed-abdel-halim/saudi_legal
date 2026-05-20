<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AzureSearchService;
use App\Services\AzureBlobService;
use Illuminate\Http\JsonResponse;

/**
 * API endpoints لمراقبة حالة Azure (للـ Admin Dashboard)
 *
 * GET /api/azure/status        → حالة عامة لكل الخدمات
 * GET /api/azure/search/stats  → إحصائيات الـ Search Index
 * GET /api/azure/search/test   → اختبار بحث سريع
 */
class AzureStatusController extends Controller
{
    public function __construct(
        protected AzureSearchService $search,
        protected AzureBlobService   $blob
    ) {}

    /**
     * GET /api/azure/status
     * حالة شاملة لكل خدمات Azure
     */
    public function status(): JsonResponse
    {
        $searchEnabled  = config('azure.search.enabled', false);
        $searchEndpoint = config('azure.search.endpoint', '');
        $storageEnabled = ! empty(config('azure.storage.name'));

        $searchStats = $searchEnabled ? $this->search->getIndexStats() : null;

        return response()->json([
            'azure' => [
                'search' => [
                    'enabled'        => $searchEnabled,
                    'configured'     => ! empty($searchEndpoint),
                    'index'          => config('azure.search.index'),
                    'document_count' => $searchStats['documentCount'] ?? null,
                    'storage_bytes'  => $searchStats['storageSize'] ?? null,
                    'status'         => $searchStats ? 'connected' : ($searchEnabled ? 'error' : 'disabled'),
                ],
                'blob_storage' => [
                    'enabled'    => $storageEnabled,
                    'account'    => config('azure.storage.name') ?: 'not configured',
                    'containers' => config('azure.storage.containers'),
                    'status'     => $storageEnabled ? 'configured' : 'disabled',
                ],
            ],
            'search_mode' => $searchEnabled ? 'azure_vector' : 'keyword_fallback',
            'timestamp'   => now()->toIso8601String(),
        ]);
    }

    /**
     * GET /api/azure/search/test?q=نفقة
     * اختبار سريع للبحث
     */
    public function testSearch(\Illuminate\Http\Request $request): JsonResponse
    {
        $query = $request->input('q', 'نفقة الزوجة');

        $start   = microtime(true);
        $results = $this->search->hybridSearch($query, 3);
        $elapsed = round((microtime(true) - $start) * 1000, 2);

        return response()->json([
            'query'         => $query,
            'mode'          => config('azure.search.enabled') ? 'azure_vector' : 'keyword_fallback',
            'results_count' => $results->count(),
            'elapsed_ms'    => $elapsed,
            'results'       => $results->map(fn($r) => [
                'question'        => mb_substr($r->question ?? '', 0, 100),
                'source_type'     => $r->source_type ?? '',
                'relevance_score' => $r->relevance_score ?? 0,
            ])->values(),
        ]);
    }
}
