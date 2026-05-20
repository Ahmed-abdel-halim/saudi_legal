<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AzureSearchService;
use App\Services\AzureBlobService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class AzureManagementController extends Controller
{
    public function __construct(
        protected AzureSearchService $search,
        protected AzureBlobService   $blob
    ) {}

    public function index(Request $request)
    {
        $searchEnabled  = config('azure.search.enabled', false);
        $searchEndpoint = config('azure.search.endpoint', '');
        $storageEnabled = ! empty(config('azure.storage.name'));

        // جلب الإحصائيات إذا كان الاتصال متاحاً
        $searchStats = null;
        $connectionOk = false;
        if ($searchEnabled && !empty($searchEndpoint)) {
            $searchStats = $this->search->getIndexStats();
            $connectionOk = !is_null($searchStats);
        }

        // اختبار بحث تجريبي
        $testQuery   = $request->input('q', 'نفقة الزوجة');
        $testResults = null;
        $elapsedMs   = 0;

        if ($request->has('q') && $searchEnabled && $connectionOk) {
            $start = microtime(true);
            $testResults = $this->search->hybridSearch($testQuery, 3);
            $elapsedMs = round((microtime(true) - $start) * 1000, 2);
        }

        return view('admin.azure.index', compact(
            'searchEnabled',
            'searchEndpoint',
            'storageEnabled',
            'searchStats',
            'connectionOk',
            'testQuery',
            'testResults',
            'elapsedMs'
        ));
    }

    public function sync(Request $request)
    {
        $type = $request->input('type', 'all');

        try {
            // تشغيل الأمر في الخلفية لتفادي الـ timeout
            Artisan::queue('azure:index-legal', [
                'type' => $type,
                '--create-index' => true,
            ]);

            return back()->with('success', 'تم بدء عملية مزامنة وفهرسة البيانات القانونية في الخلفية بنجاح!');
        } catch (\Throwable $e) {
            return back()->with('error', 'فشل بدء المزامنة: ' . $e->getMessage());
        }
    }
}
