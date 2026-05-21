<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\LegalQaPair;

$currentQA = LegalQaPair::find(17);
$citations = $currentQA->citations;

$mentionedArticles = $citations->map(function($c) {
    if ($c->article) return $c->article;
    
    $system = trim($c->system_name ?? '');

    // Skip empty, very short, or pure-number/date strings
    if (mb_strlen($system) < 15) {
        echo "Skipped '{$system}' because length < 15\n";
        return null;
    }

    // Let's see what happens without the aggressive filter
    $isPrinciple = str_contains($system, 'مبدأ قضائي')
        || str_contains($system, 'المبدأ')
        || str_contains($system, 'قاعدة قضائية')
        || str_contains($system, 'مستنبط')
        || str_contains($system, 'فقهاء');
        
    $isSharia = str_contains($system, 'القاعدة الشرعية')
        || str_contains($system, 'قاعدة فقهية')
        || str_contains($system, 'الفقهاء')
        || str_contains($system, 'قوله تعالى')
        || str_contains($system, 'قال تعالى')
        || str_contains($system, 'الحديث')
        || str_contains($system, 'الآية');
        
    $isContract = str_contains($system, 'العقد')
        || str_contains($system, 'اتفاقية')
        || str_contains($system, 'البند')
        || str_contains($system, 'بند')
        || str_contains($system, 'ملحق');
        
    $isEvidence = str_contains($system, 'محضر')
        || str_contains($system, 'إفادة')
        || str_contains($system, 'خطاب')
        || str_contains($system, 'تقرير')
        || str_contains($system, 'سند')
        || str_contains($system, 'فاتورة')
        || str_contains($system, 'كشف');

    if ($isPrinciple) {
        $cleanTitle = str_replace(['مبدأ قضائي:', 'مبدأ قضائي :', 'مبدأ قضائي مستقر في'], '', $system);
        $cleanTitle = trim($cleanTitle);
        return (object) [
            'id' => 'temp-' . $c->id,
            'legislation_title' => 'مبدأ قضائي مرتبط',
            'article_title' => '',
            'content' => $cleanTitle
        ];
    } elseif ($isSharia) {
        $cleanTitle = str_replace(['القاعدة الشرعية:', 'القاعدة الشرعية :'], '', $system);
        $cleanTitle = trim($cleanTitle);
        return (object) [
            'id' => 'temp-' . $c->id,
            'legislation_title' => 'مستند شرعي / فقهي',
            'article_title' => '',
            'content' => $cleanTitle
        ];
    } elseif ($isContract) {
        return (object) [
            'id' => 'temp-' . $c->id,
            'legislation_title' => 'مستند تعاقدي / العقد المبرم',
            'article_title' => '',
            'content' => $system
        ];
    } elseif ($isEvidence) {
        return (object) [
            'id' => 'temp-' . $c->id,
            'legislation_title' => 'بيّنة / مستند إثبات',
            'article_title' => '',
            'content' => $system
        ];
    } else {
        $isLawWord = str_contains($system, 'نظام')
            || str_contains($system, 'قانون')
            || str_contains($system, 'لائحة')
            || str_contains($system, 'مرسوم')
            || str_contains($system, 'قرار');
        if ($isLawWord && mb_strlen($system) < 150) {
            return (object) [
                'id' => 'temp-' . $c->id,
                'legislation_title' => $system,
                'article_title' => $c->article_number ? "المادة {$c->article_number}" : 'مادة غير محددة',
                'content' => 'نص المادة غير متوفر حالياً في قاعدة البيانات. المرجع: ' . $system . ($c->article_number ? "، المادة {$c->article_number}" : '')
            ];
        } else {
            if (mb_strlen($system) > 200) {
                echo "Skipped '{$system}' because it's too long (>200) and unclassified\n";
                return null;
            }
            return (object) [
                'id' => 'temp-' . $c->id,
                'legislation_title' => 'مستند وقائع / أسباب الحكم',
                'article_title' => '',
                'content' => $system
            ];
        }
    }
})->filter()->unique(fn($a) => is_object($a) ? ($a->id ?? $a->legislation_title) : $a);

echo "Total mentioned articles: " . $mentionedArticles->count() . "\n";
foreach ($mentionedArticles as $ma) {
    echo "ID: {$ma->id} | Title: {$ma->legislation_title} | Article Title: {$ma->article_title} | Content: {$ma->content}\n";
}
