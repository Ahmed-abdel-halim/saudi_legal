<?php

namespace Tests\Feature;

use App\Models\PublicLegalAnswer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class LegalSitemapTest extends TestCase
{
    use RefreshDatabase;

    public function test_sitemap_command_generates_urls_with_main_domain_and_no_subdomain(): void
    {
        // Ensure at least one record exists
        PublicLegalAnswer::create([
            'slug' => 'test-sitemap-item-' . uniqid(),
            'locale' => 'ar',
            'question' => 'اختبار sitemap',
            'answer' => 'إجابة اختبار',
            'status' => 'published',
        ]);

        $this->artisan('sitemap:legal-qa', ['--chunk' => 5000])
            ->assertExitCode(0);

        $indexPath = public_path('sitemap-legal-index.xml');
        $this->assertFileExists($indexPath);

        $indexContent = File::get($indexPath);
        $this->assertStringContainsString('https://radiif.com/sitemap-legal-qa-1.xml', $indexContent);
        $this->assertStringNotContainsString('saudilegal.radiif.com', $indexContent);
        $this->assertStringNotContainsString('<loc></loc>', $indexContent);

        $subSitemapPath = public_path('sitemap-legal-qa-1.xml');
        $this->assertFileExists($subSitemapPath);

        $subContent = File::get($subSitemapPath);
        $this->assertStringContainsString('https://radiif.com/legal-qa/', $subContent);
        $this->assertStringNotContainsString('saudilegal.radiif.com', $subContent);
    }
}
