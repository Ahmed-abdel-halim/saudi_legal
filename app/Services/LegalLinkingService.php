<?php

namespace App\Services;

use App\Models\LegalArticle;
use Illuminate\Support\Facades\DB;

class LegalLinkingService
{
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
                $query = LegalArticle::where('legislation_title', 'LIKE', "%$lawName%");
                
                if ($articleNum) {
                    $article = (clone $query)->where('article_title', 'LIKE', "%$articleNum%")->first();
                    if ($article) {
                        return [
                            'system_name' => $article->legislation_title,
                            'article_number' => $articleNum,
                            'article_text' => $article->content,
                            'confidence' => 100
                        ];
                    }
                }
                
                // إذا لم نجد رقم المادة، نحاول البحث بالكلمات المفتاحية داخل هذا النظام
                $article = (clone $query)->where(function($q) use ($text) {
                    $keywords = ['اليمين الحاسمة', 'الشخصية الاعتبارية', 'الشركات', 'الشرط الجزائي', 'فسخ العقد'];
                    foreach ($keywords as $word) {
                        if (mb_stripos($text, $word) !== false) {
                            $q->orWhere('content', 'LIKE', "%$word%");
                        }
                    }
                })->first();

                if ($article) {
                    $result['system_name'] = $article->legislation_title;
                    $result['article_number'] = $article->article_title;
                    $result['article_text'] = $article->content;
                    $result['confidence'] = 90;
                    return $result;
                }
                
                // fallback to first article
                $article = $query->first();
                if ($article) {
                    $result['system_name'] = $article->legislation_title;
                    $result['article_text'] = $article->content;
                    $result['confidence'] = 80;
                }
            }
        }

        // 4. الربط بناءً على الكلمات المفتاحية (Contextual Matching)
        foreach ($contextualRules as $lawName => $keywords) {
            foreach ($keywords as $keyword) {
                if (mb_stripos($text, $keyword) !== false) {
                    $query = LegalArticle::where('legislation_title', 'LIKE', "%$lawName%");
                    
                    if ($articleNum) {
                        $query->where('article_title', 'LIKE', "%$articleNum%");
                    }
                    
                    $article = $query->first();
                    if ($article) {
                        $result['system_name'] = $article->legislation_title;
                        $result['article_number'] = $articleNum ?? '1';
                        $result['article_text'] = $article->content;
                        $result['confidence'] = 60;
                        return $result;
                    }
                }
            }
        }

        return $result;
    }
}
