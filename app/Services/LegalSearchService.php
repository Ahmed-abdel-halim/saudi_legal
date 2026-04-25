<?php

namespace App\Services;

use App\Models\LegalArticle;
use Illuminate\Support\Facades\DB;

class LegalSearchService
{
    public function search($query, $limit = 5)
    {
        $keywords = $this->extractKeywords($query);
        
        if (empty($keywords)) {
            return collect();
        }

        $queryBuilder = LegalArticle::query();
        
        $queryBuilder->where(function($q) use ($keywords) {
            foreach ($keywords as $keyword) {
                $q->orWhere('content', 'LIKE', '%' . $keyword . '%')
                  ->orWhere('legislation_title', 'LIKE', '%' . $keyword . '%');
            }
        });

        $articles = $queryBuilder->limit(50)->get();

        $articles = $articles->sortByDesc(function ($article) use ($keywords) {
            $score = 0;
            foreach ($keywords as $keyword) {
                if (mb_stripos($article->content, $keyword) !== false) {
                    $score += 1;
                }
                if (mb_stripos($article->legislation_title, $keyword) !== false) {
                    $score += 2;
                }
            }
            return $score;
        });

        return $articles->take($limit)->values();
    }

    private function extractKeywords($query)
    {
        $stopWords = ['ما', 'هي', 'ماذا', 'كيف', 'هل', 'في', 'من', 'على', 'عن', 'إلى', 'مع', 'متى', 'أين', 'لماذا', 'كم', 'أي'];
        $symbols = ['؟', '!', '.', ',', '،', ':', '؛'];
        
        $cleanQuery = str_replace($symbols, ' ', $query);
        
        $words = preg_split('/\s+/', $cleanQuery, -1, PREG_SPLIT_NO_EMPTY);
        
        $keywords = [];
        foreach ($words as $word) {
            if (!in_array($word, $stopWords) && mb_strlen($word) > 2) {
                $strippedWord = preg_replace('/^(ال|ب|ل|ك|ف|و)/', '', $word);
                if (mb_strlen($strippedWord) >= 3) {
                    $keywords[] = $strippedWord;
                }
                $keywords[] = $word;
            }
        }
        
        return array_unique($keywords);
    }

    public function semanticSearch($query)
    {
        return $this->search($query);
    }
}
