<?php

namespace App\Services;

use App\Models\LegalArticle;
use Illuminate\Support\Facades\DB;

class LegalSearchService
{
    /**
     * البحث عن أكثر المواد القانونية صلة بالسؤال
     */
    public function search($query, $limit = 5)
    {
        // تنظيف النص للبحث
        $keywords = $this->extractKeywords($query);
        
        if (empty($keywords)) {
            return collect();
        }

        // استخدام البحث النصي بمرونة (OR) للبحث في 16 ألف مادة
        $queryBuilder = LegalArticle::query();
        
        $queryBuilder->where(function($q) use ($keywords) {
            foreach ($keywords as $keyword) {
                $q->orWhere('content', 'LIKE', '%' . $keyword . '%')
                  ->orWhere('legislation_title', 'LIKE', '%' . $keyword . '%');
            }
        });

        // جلب عينة واسعة لترتيبها حسب الأهمية في الذاكرة
        $articles = $queryBuilder->limit(50)->get();

        // خوارزمية ترتيب النتائج حسب الأهمية (Scoring Algorithm)
        $articles = $articles->sortByDesc(function ($article) use ($keywords) {
            $score = 0;
            foreach ($keywords as $keyword) {
                if (mb_stripos($article->content, $keyword) !== false) {
                    $score += 1;
                }
                if (mb_stripos($article->legislation_title, $keyword) !== false) {
                    $score += 2; // الكلمة في عنوان النظام لها وزن أكبر
                }
            }
            return $score;
        });

        return $articles->take($limit)->values();
    }

    /**
     * استخراج الكلمات المفتاحية من السؤال وتنقيتها
     */
    private function extractKeywords($query)
    {
        // حذف كلمات التوقف الشائعة وعلامات الترقيم
        $stopWords = ['ما', 'هي', 'ماذا', 'كيف', 'هل', 'في', 'من', 'على', 'عن', 'إلى', 'مع', 'متى', 'أين', 'لماذا', 'كم', 'أي'];
        $symbols = ['؟', '!', '.', ',', '،', ':', '؛'];
        
        $cleanQuery = str_replace($symbols, ' ', $query);
        
        // تفكيك النص
        $words = preg_split('/\s+/', $cleanQuery, -1, PREG_SPLIT_NO_EMPTY);
        
        $keywords = [];
        foreach ($words as $word) {
            if (!in_array($word, $stopWords) && mb_strlen($word) > 2) {
                // إزالة (الـ) التعريفية و (بـ) من بداية الكلمة لتحسين المطابقة
                $strippedWord = preg_replace('/^(ال|ب|ل|ك|ف|و)/', '', $word);
                if (mb_strlen($strippedWord) >= 3) {
                    $keywords[] = $strippedWord;
                }
                $keywords[] = $word;
            }
        }
        
        return array_unique($keywords);
    }

    /**
     * البحث المتقدم (يمكن لاحقاً ربطه بـ Vector DB مثل Pinecone)
     */
    public function semanticSearch($query)
    {
        // هذه المساحة مخصصة لربط Embeddings لاحقاً
        return $this->search($query);
    }
}
