<?php

namespace App\Services;

use App\Models\LegalTask;
use Illuminate\Support\Facades\DB;

class LegalSearchService
{
    /**
     * Search for relevant legal context in Judgments/Tasks
     */
    public function search($query, $limit = 5)
    {
        $keywords = $this->extractKeywords($query);
        
        if (empty($keywords)) {
            return collect();
        }

        $queryBuilder = LegalTask::query();
        
        // Search in question, correct_answer, and case_text
        $queryBuilder->where(function($q) use ($keywords) {
            foreach ($keywords as $keyword) {
                $q->orWhere('question', 'LIKE', '%' . $keyword . '%')
                  ->orWhere('case_text', 'LIKE', '%' . $keyword . '%')
                  ->orWhere('correct_answer', 'LIKE', '%' . $keyword . '%');
            }
        });

        $tasks = $queryBuilder->limit(50)->get();

        // Rank by keyword frequency and relevance
        $tasks = $tasks->sortByDesc(function ($task) use ($keywords) {
            $score = 0;
            foreach ($keywords as $keyword) {
                if ($task->question && mb_stripos($task->question, $keyword) !== false) {
                    $score += 3; // Question match is highly relevant
                }
                if ($task->case_text && mb_stripos($task->case_text, $keyword) !== false) {
                    $score += 2;
                }
                if ($task->correct_answer && mb_stripos($task->correct_answer, $keyword) !== false) {
                    $score += 1;
                }
            }
            return $score;
        });

        return $tasks->take($limit)->values();
    }

    private function extractKeywords($query)
    {
        $stopWords = ['ما', 'هي', 'ماذا', 'كيف', 'هل', 'في', 'من', 'على', 'عن', 'إلى', 'مع', 'متى', 'أين', 'لماذا', 'كم', 'أي', 'قضية', 'حكم'];
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
        // Fallback to keyword search for now
        return $this->search($query);
    }
}
