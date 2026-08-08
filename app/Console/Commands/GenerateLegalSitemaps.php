<?php

namespace App\Console\Commands;

use App\Models\PublicLegalAnswer;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class GenerateLegalSitemaps extends Command
{
    /**
     * اسم ووصف الأمر
     */
    protected $signature = 'sitemap:legal-qa
                            {--chunk=5000 : عدد الروابط في كل ملف sitemap}
                            {--locale= : تصفية بلغة معينة (ar, en, أو فارغ للكل)}';

    protected $description = 'توليد ملفات XML Sitemaps للأسئلة القانونية العامة (50,000 سؤال) مع دعم اللغتين';

    public function handle(): int
    {
        $chunkSize = (int) $this->option('chunk');
        $locale    = $this->option('locale');

        $this->info("⚙️  بدء توليد ملفات Sitemap (chunk: {$chunkSize}) ...");

        $query = PublicLegalAnswer::query();
        if ($locale) {
            $query->where('locale', $locale);
        }

        $totalRecords = $query->count();
        $this->info("📊 إجمالي السجلات: {$totalRecords}");

        $fileIndex   = 1;
        $sitemapIndex = [];

        $query->chunk($chunkSize, function ($answers) use (&$fileIndex, &$sitemapIndex) {
            $xml  = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
            $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"' . PHP_EOL;
            $xml .= '        xmlns:xhtml="http://www.w3.org/1999/xhtml">' . PHP_EOL;

            foreach ($answers as $answer) {
                $url = $answer->locale === 'en'
                    ? route('public.qa.en', $answer->slug)
                    : route('public.qa.ar', $answer->slug);
                $url = $this->formatUrl($url);

                $xml .= '  <url>' . PHP_EOL;
                $xml .= '    <loc>' . htmlspecialchars($url) . '</loc>' . PHP_EOL;
                $xml .= '    <lastmod>' . $answer->updated_at->toAtomString() . '</lastmod>' . PHP_EOL;
                $xml .= '    <changefreq>weekly</changefreq>' . PHP_EOL;
                $xml .= '    <priority>0.8</priority>' . PHP_EOL;

                // إضافة hreflang إذا كانت هناك نسخة مقابلة
                if ($answer->counterpart_slug) {
                    $targetLocale = $answer->locale === 'ar' ? 'en' : 'ar';
                    $altRouteName = $targetLocale === 'en' ? 'public.qa.en' : 'public.qa.ar';
                    try {
                        $altUrl = route($altRouteName, $answer->counterpart_slug);
                        $altUrl = $this->formatUrl($altUrl);
                        $xml .= '    <xhtml:link rel="alternate" hreflang="' . $answer->locale . '" href="' . htmlspecialchars($url) . '"/>' . PHP_EOL;
                        $xml .= '    <xhtml:link rel="alternate" hreflang="' . $targetLocale . '" href="' . htmlspecialchars($altUrl) . '"/>' . PHP_EOL;
                    } catch (\Exception $e) {
                        // تجاهل الأخطاء في توليد الروابط
                    }
                }

                $xml .= '  </url>' . PHP_EOL;
            }

            $xml .= '</urlset>';

            $filename = "sitemap-legal-qa-{$fileIndex}.xml";
            File::put(public_path($filename), $xml);
            $sitemapIndex[] = $filename;

            $this->line("  ✅ تم إنشاء: {$filename} ({$answers->count()} رابط)");
            $fileIndex++;
        });

        // إنشاء Sitemap Index يجمع كل الملفات
        $this->generateSitemapIndex($sitemapIndex);

        $this->info("🎉 تم الانتهاء! تم إنشاء " . count($sitemapIndex) . " ملف sitemap.");
        $this->line("   أرفع الملف sitemap-legal-index.xml إلى Google Search Console.");

        return self::SUCCESS;
    }

    /**
     * إنشاء ملف Sitemap Index يجمع كل الملفات الفرعية
     */
    private function generateSitemapIndex(array $sitemapFiles): void
    {
        $xml  = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
        $xml .= '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . PHP_EOL;

        foreach ($sitemapFiles as $filename) {
            $filename = trim((string) $filename);
            if (empty($filename)) {
                continue;
            }
            $url  = $this->formatUrl($filename);
            $xml .= '  <sitemap>' . PHP_EOL;
            $xml .= '    <loc>' . htmlspecialchars($url) . '</loc>' . PHP_EOL;
            $xml .= '    <lastmod>' . now()->toAtomString() . '</lastmod>' . PHP_EOL;
            $xml .= '  </sitemap>' . PHP_EOL;
        }

        $xml .= '</sitemapindex>';

        File::put(public_path('sitemap-legal-index.xml'), $xml);
        $this->line("  📋 تم إنشاء: sitemap-legal-index.xml (Sitemap Index)");
    }

    /**
     * توحيد وتنظيف رابط Sitemap لضمان استخدام النطاق الرئيسي (https://radiif.com) وإزالة أي Subdomain مثل saudilegal.radiif.com
     */
    private function formatUrl(string $url): string
    {
        $baseUrl = rtrim(config('app.sitemap_domain', env('SITEMAP_BASE_URL', 'https://radiif.com')), '/');

        $parsed = parse_url($url);
        $path = $parsed['path'] ?? '';
        if (isset($parsed['query'])) {
            $path .= '?' . $parsed['query'];
        }

        $path = '/' . ltrim($path, '/');
        return $baseUrl . $path;
    }
}
