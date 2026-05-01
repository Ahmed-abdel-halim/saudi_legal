<?php

namespace App\Services;

use App\Models\LegalArticle;
use Illuminate\Support\Facades\DB;

class LegalLinkingService
{
    /**
     * محاولة إيجاد أفضل مادة قانونية مطابقة بناءً على النص
     */
    private static $lawsCache = [];

    /**
     * محاولة إيجاد أفضل مادة قانونية مطابقة بناءً على النص
     */
    public function findBestMatch($text)
    {
        $result = [
            'system_name' => 'نظام سعودي',
            'article_number' => 'غير محدد',
            'article_text' => 'يرجى مراجعة نص المادة يدوياً.',
            'confidence' => 0
        ];

        if (empty($text))
            return $result;

        // 1. استخراج رقم المادة (يدعم الأرقام)
        $articleNum = null;
        if (preg_match('/المادة\s+(\d+)/u', $text, $artMatches)) {
            $articleNum = $artMatches[1];
            $result['article_number'] = $articleNum;
        }

        // 2. استخراج النظام من الكلمات المفتاحية والسياق
        $contextualRules = [
            'الإثبات' => ['بينة', 'يمين', 'إقرار', 'شهادة', 'محرر', 'فواتير', 'إثبات'],
            'المحاكم التجارية' => ['منازعة تجارية', 'مديونية', 'تاجر', 'شركات', 'بضائع'],
            'المعاملات المدنية' => ['عقد', 'شرط جزائي', 'تعويض', 'فسخ', 'التزام'],
            'الشركات' => ['جمعية عمومية', 'شريك', 'مدير', 'رأس مال', 'تأسيس'],
            'المرافعات الشرعية' => ['اختصاص', 'تبليغ', 'خصومة', 'دفع'],
        ];

        // 3. التحقق مما إذا كان هناك نظام مذكور صراحة في النص (مثل "نظام الإثبات")
        foreach ($contextualRules as $lawName => $keywords) {
            if (mb_stripos($text, "نظام $lawName") !== false || mb_stripos($text, "النظام $lawName") !== false) {
                // استخدام الكاش لتقليل الاستعلامات
                $cacheKey = "exact_{$lawName}_{$articleNum}";
                if (isset(self::$lawsCache[$cacheKey])) {
                    return self::$lawsCache[$cacheKey];
                }

                $query = LegalArticle::where('legislation_title', 'LIKE', "%$lawName%");

                if ($articleNum) {
                    $article = (clone $query)->where('article_title', 'LIKE', "%$articleNum%")->first();
                    if ($article) {
                        $match = [
                            'system_name' => $article->legislation_title,
                            'article_number' => $articleNum,
                            'article_text' => $article->content,
                            'confidence' => 100
                        ];
                        self::$lawsCache[$cacheKey] = $match;
                        return $match;
                    }
                }

                // Fallback to basic keyword search if no article num found or matching
                $article = $query->limit(1)->first();
                if ($article) {
                    $result['system_name'] = $article->legislation_title;
                    $result['article_text'] = $article->content;
                    $result['confidence'] = 80;
                    // No return here, keep searching for better match
                }
            }
        }

        // 4. استخراج رقم القضية من النص (تم تعطيله لمنع تداخل الأحكام مع النصوص النظامية)
        /*
        if (preg_match('/حكم\s+رقم\s+([0-9.]+)/u', $text, $caseMatches)) {
            $caseNum = preg_replace('/[^0-9]/', '', $caseMatches[1]);
            $originalTask = \App\Models\LegalTask::where('case_reference', 'LIKE', "%$caseNum%")
                ->whereNotNull('case_text')
                ->limit(1)
                ->first();
            if ($originalTask) {
                $result['article_text'] = $originalTask->case_text;
            }
        }
        */

        // 5. الربط بناءً على الكلمات المفتاحية (Contextual Matching)
        // نقوم بجمع الكلمات المفتاحية الموجودة في النص أولاً لتجنب الاستعلامات غير الضرورية
        foreach ($contextualRules as $lawName => $keywords) {
            $keywordFound = false;
            foreach ($keywords as $keyword) {
                if (mb_stripos($text, $keyword) !== false) {
                    $keywordFound = true;
                    break;
                }
            }

            if ($keywordFound) {
                $cacheKey = "context_{$lawName}_{$articleNum}";
                if (isset(self::$lawsCache[$cacheKey])) {
                    $cached = self::$lawsCache[$cacheKey];
                    if ($cached['confidence'] >= 85)
                        return $cached;
                    continue;
                }

                $query = LegalArticle::where('legislation_title', 'LIKE', "%$lawName%");
                if ($articleNum) {
                    $query->where('article_title', 'LIKE', "%$articleNum%");
                }

                $article = $query->first();
                if ($article) {
                    $match = [
                        'system_name' => $article->legislation_title,
                        'article_number' => $articleNum ?? '1',
                        'article_text' => $article->content,
                        'confidence' => 85,
                    ];
                    self::$lawsCache[$cacheKey] = $match;
                    return $match;
                }
            }
        }

        return $result;
    }
}
