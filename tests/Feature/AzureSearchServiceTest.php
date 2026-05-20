<?php

namespace Tests\Feature;

use App\Services\AzureSearchService;
use App\Services\LegalSearchService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class AzureSearchServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush(); // تنظيف embedding cache بين الاختبارات
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  1. Fallback عند عدم تفعيل Azure
    // ─────────────────────────────────────────────────────────────────────────

    /** @test */
    public function it_falls_back_to_keyword_search_when_azure_disabled()
    {
        config(['azure.search.enabled' => false]);

        $service = app(AzureSearchService::class);
        $results = $service->hybridSearch('نفقة الزوجة', 5);

        // يجب أن يُرجع Collection (حتى لو فارغ — لأنه fallback)
        $this->assertInstanceOf(\Illuminate\Support\Collection::class, $results);
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  2. Fallback عند فشل Azure API
    // ─────────────────────────────────────────────────────────────────────────

    /** @test */
    public function it_falls_back_gracefully_when_azure_api_fails()
    {
        config([
            'azure.search.enabled'  => true,
            'azure.search.endpoint' => 'https://fake-endpoint.search.windows.net',
            'azure.search.key'      => 'fake-key',
        ]);

        // محاكاة فشل Gemini embedding
        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([], 500),
            'fake-endpoint.search.windows.net/*'   => Http::response([], 503),
        ]);

        $service = app(AzureSearchService::class);
        $results = $service->hybridSearch('طلاق', 3);

        // لا يجب أن يرمي Exception — يُرجع Collection فارغ أو keyword results
        $this->assertInstanceOf(\Illuminate\Support\Collection::class, $results);
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  3. نجاح البحث مع Fake HTTP
    // ─────────────────────────────────────────────────────────────────────────

    /** @test */
    public function it_returns_parsed_results_from_azure_search()
    {
        config([
            'azure.search.enabled'  => true,
            'azure.search.endpoint' => 'https://radiif.search.windows.net',
            'azure.search.key'      => 'test-admin-key',
        ]);

        // Fake embedding response
        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'embedding' => ['values' => array_fill(0, 768, 0.1)],
            ], 200),

            // Fake Azure Search response
            'radiif.search.windows.net/*' => Http::response([
                '@odata.count' => 2,
                'value' => [
                    [
                        'id'             => 'task_1',
                        'question'       => 'ما هي شروط النفقة؟',
                        'answer'         => 'النفقة واجبة على الزوج...',
                        'domain'         => 'family',
                        'source_type'    => 'judgment',
                        'case_reference' => 'قضية 1234',
                        'law_system'     => 'نظام الأحوال الشخصية',
                        '@search.score'  => 0.95,
                    ],
                    [
                        'id'             => 'article_5',
                        'question'       => 'المادة 55 - نفقة الزوجة',
                        'answer'         => 'تجب النفقة للزوجة على زوجها...',
                        'domain'         => 'law_article',
                        'source_type'    => 'article',
                        'case_reference' => 'مادة رقم 55',
                        'law_system'     => 'نظام الأحوال الشخصية',
                        '@search.score'  => 0.88,
                    ],
                ],
            ], 200),
        ]);

        $service = app(AzureSearchService::class);
        $results = $service->hybridSearch('نفقة الزوجة', 5);

        $this->assertCount(2, $results);

        // تحقق من تحويل النتائج للصيغة الصحيحة
        $first = $results->first();
        $this->assertEquals('ما هي شروط النفقة؟', $first->question);
        $this->assertEquals('judgment', $first->source_type);
        $this->assertEquals(0.95, $first->relevance_score);
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  4. LegalAiController يستخدم Azure عند تفعيله
    // ─────────────────────────────────────────────────────────────────────────

    /** @test */
    public function legal_ai_controller_uses_azure_search_when_enabled()
    {
        config(['azure.search.enabled' => true]);

        // نعمل mock للـ AzureSearchService
        $mockAzure = \Mockery::mock(AzureSearchService::class);
        $mockAzure->shouldReceive('hybridSearch')
            ->once()
            ->with(\Mockery::type('string'), 5)
            ->andReturn(collect());

        $this->app->instance(AzureSearchService::class, $mockAzure);

        $this->post(route('legal-ai.ask'), ['question' => 'ما هي شروط الطلاق؟'])
            ->assertStatus(200);
    }

    /** @test */
    public function legal_ai_controller_uses_keyword_search_when_azure_disabled()
    {
        config(['azure.search.enabled' => false]);

        // نتأكد أن AzureSearchService لم يُستدعَ
        $mockAzure = \Mockery::mock(AzureSearchService::class);
        $mockAzure->shouldNotReceive('hybridSearch');

        $this->app->instance(AzureSearchService::class, $mockAzure);

        $this->post(route('legal-ai.ask'), ['question' => 'ما هي شروط الطلاق؟'])
            ->assertStatus(200);
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  5. Azure Status API
    // ─────────────────────────────────────────────────────────────────────────

    /** @test */
    public function azure_status_endpoint_returns_correct_structure()
    {
        $admin = \App\Models\User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin, 'sanctum')
            ->getJson('/api/azure/status')
            ->assertStatus(200)
            ->assertJsonStructure([
                'azure' => [
                    'search' => ['enabled', 'configured', 'status'],
                    'blob_storage' => ['enabled', 'status'],
                ],
                'search_mode',
                'timestamp',
            ]);
    }
}
