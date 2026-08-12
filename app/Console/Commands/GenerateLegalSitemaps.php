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
                            {--locale=    : تصفية بلغة معينة (ar, en، أو فارغ للكل)}';

    protected $description = 'توليد ملفات XML Sitemaps للأسئلة القانونية العامة مع ملفات منفصلة للعربي والإنجليزي';

    public function handle(): int
    {
        $chunkSize = (int) $this->option('chunk');
        $locale    = $this->option('locale');

        $this->info("⚙️  بدء توليد ملفات Sitemap (chunk: {$chunkSize}) ...");

        $allIndexFiles = [];

        // توليد سيتماب العربي
        if (!$locale || $locale === 'ar') {
            $arFiles = $this->generateForLocale('ar', $chunkSize);
            $allIndexFiles = array_merge($allIndexFiles, $arFiles);

            if (!empty($arFiles)) {
                $this->generateSitemapIndex($arFiles, 'sitemap-legal-ar-index.xml');
                $this->line("  📋 تم إنشاء: sitemap-legal-ar-index.xml ({$this->countUrls($arFiles)} رابط عربي)");
            }
        }

        // توليد سيتماب الإنجليزي
        if (!$locale || $locale === 'en') {
            $enFiles = $this->generateForLocale('en', $chunkSize);
            $allIndexFiles = array_merge($allIndexFiles, $enFiles);

            if (!empty($enFiles)) {
                $this->generateSitemapIndex($enFiles, 'sitemap-legal-en-index.xml');
                $this->line("  📋 تم إنشاء: sitemap-legal-en-index.xml ({$this->countUrls($enFiles)} رابط إنجليزي)");
            }
        }

        // سيتماب موحّد يجمع الكل (للتوافق مع الإعداد السابق في Google Search Console)
        if (!$locale && !empty($allIndexFiles)) {
            $this->generateSitemapIndex($allIndexFiles, 'sitemap-legal-index.xml');
            $this->line("  📋 تم تحديث: sitemap-legal-index.xml (فهرس موحّد)");
        }

        $this->info("🎉 تم الانتهاء! أرفع الملفات إلى Google Search Console:");
        $this->line("   • sitemap-legal-ar-index.xml  ← للصفحات العربية");
        $this->line("   • sitemap-legal-en-index.xml  ← للصفحات الإنجليزية");

        return self::SUCCESS;
    }

    /**
     * توليد ملفات Sitemap للغة محددة
     * يُرجع قائمة أسماء الملفات المُنشأة
     */
    private function generateForLocale(string $locale, int $chunkSize): array
    {
        $langLabel    = $locale === 'ar' ? 'عربي' : 'إنجليزي';
        $filePrefix   = "sitemap-legal-{$locale}";
        $routeName    = $locale === 'en' ? 'public.qa.en' : 'public.qa.ar';
        $altRouteName = $locale === 'en' ? 'public.qa.ar' : 'public.qa.en';
        $hreflangAlt  = $locale === 'en' ? 'ar' : 'en';

        // كل السجلات موجوة في الجدول الأساسي (سواء locale=ar أو بدون تصفية)
        $query = PublicLegalAnswer::query();

        $totalRecords = $query->count();
        $this->info("📊 [{$langLabel}] السجلات: {$totalRecords}");

        if ($totalRecords === 0) {
            $this->warn("  ⚠️  لا توجد سجلات {$langLabel} — تأكد من تشغيل: php artisan seo:translate-en");
            return [];
        }

        $fileIndex    = 1;
        $createdFiles = [];

        $query->orderBy('id')->chunk($chunkSize, function ($answers) use (
            &$fileIndex, &$createdFiles, $filePrefix, $routeName, $altRouteName, $locale, $hreflangAlt, $langLabel
        ) {
            $xml  = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
            $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"' . PHP_EOL;
            $xml .= '        xmlns:xhtml="http://www.w3.org/1999/xhtml">' . PHP_EOL;

            foreach ($answers as $answer) {
                try {
                    $url = $this->formatUrl(route($routeName, $answer->slug));
                } catch (\Exception $e) {
                    continue;
                }

                // أولوية أعلى للإنجليزي (أقل تنافسية = فرصة ظهور أكبر في AI)
                $priority = $locale === 'en' ? '0.9' : '0.8';

                $xml .= '  <url>' . PHP_EOL;
                $xml .= '    <loc>' . htmlspecialchars($url) . '</loc>' . PHP_EOL;
                $xml .= '    <lastmod>' . $answer->updated_at->toAtomString() . '</lastmod>' . PHP_EOL;
                $xml .= '    <changefreq>weekly</changefreq>' . PHP_EOL;
                $xml .= '    <priority>' . $priority . '</priority>' . PHP_EOL;

                // hreflang للنسخة المقابلة (تستخدم نفس الـ slug)
                try {
                    $altUrl = $this->formatUrl(route($altRouteName, $answer->slug));
                    $xml .= '    <xhtml:link rel="alternate" hreflang="' . $locale . '" href="' . htmlspecialchars($url) . '"/>' . PHP_EOL;
                    $xml .= '    <xhtml:link rel="alternate" hreflang="' . $hreflangAlt . '" href="' . htmlspecialchars($altUrl) . '"/>' . PHP_EOL;
                    $xml .= '    <xhtml:link rel="alternate" hreflang="x-default" href="' . htmlspecialchars($url) . '"/>' . PHP_EOL;
                } catch (\Exception $e) {
                    // تجاهل أخطاء توليد الروابط البديلة
                }

                $xml .= '  </url>' . PHP_EOL;
            }

            $xml .= '</urlset>';

            $filename = "{$filePrefix}-{$fileIndex}.xml";
            File::put(public_path($filename), $xml);
            $createdFiles[] = $filename;

            $this->line("  ✅ [{$langLabel}] تم إنشاء: {$filename} ({$answers->count()} رابط)");
            $fileIndex++;
        });

        return $createdFiles;
    }

    /**
     * إنشاء ملف Sitemap Index يجمع مجموعة ملفات
     */
    private function generateSitemapIndex(array $sitemapFiles, string $indexFilename): void
    {
        $xml  = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
        $xml .= '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . PHP_EOL;

        foreach ($sitemapFiles as $filename) {
            $filename = trim((string) $filename);
            if (empty($filename)) continue;

            $url  = $this->formatUrl($filename);
            $xml .= '  <sitemap>' . PHP_EOL;
            $xml .= '    <loc>' . htmlspecialchars($url) . '</loc>' . PHP_EOL;
            $xml .= '    <lastmod>' . now()->toAtomString() . '</lastmod>' . PHP_EOL;
            $xml .= '  </sitemap>' . PHP_EOL;
        }

        $xml .= '</sitemapindex>';

        File::put(public_path($indexFilename), $xml);
    }

    /**
     * عدّ إجمالي الروابط في قائمة ملفات Sitemap
     */
    private function countUrls(array $files): int
    {
        $total = 0;
        foreach ($files as $file) {
            $path = public_path($file);
            if (File::exists($path)) {
                $total += substr_count(File::get($path), '<loc>');
            }
        }
        return $total;
    }

    /**
     * توحيد رابط Sitemap لضمان استخدام النطاق الرئيسي (https://radiif.com)
     */
    private function formatUrl(string $url): string
    {
        $baseUrl = rtrim(config('app.sitemap_domain', env('SITEMAP_BASE_URL', 'https://radiif.com')), '/');

        $parsed = parse_url($url);
        $path   = $parsed['path'] ?? '';
        if (isset($parsed['query'])) {
            $path .= '?' . $parsed['query'];
        }

        $path = '/' . ltrim($path, '/');
        return $baseUrl . $path;
    }
}
