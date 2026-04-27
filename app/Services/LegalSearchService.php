<?php

namespace App\Services;

use App\Models\LegalTask;
use App\Models\LegalArticle;
use Illuminate\Support\Facades\DB;

class LegalSearchService
{
    /**
     * Hybrid Search (Keyword + Phrase + Contextual + Legal Logic)
     */
    public function search($query, $limit = 5)
    {
        // 1. Extract Keywords & Full Phrase
        $keywords = $this->extractKeywords($query);
        $fullPhrase = trim($query);
        
        // 2. Extract Legal References from the Question itself
        $refService = new LegalReferenceService();
        $mentionedArticles = $refService->getMentionedArticles($query);
        
        if (empty($keywords) && $mentionedArticles->isEmpty() && empty($fullPhrase)) {
            return collect();
        }

        // 3. Broad Search in Tasks/Judgments
        $queryBuilder = LegalTask::query();
        
        $queryBuilder->where(function($q) use ($keywords, $mentionedArticles, $fullPhrase) {
            // Full Phrase Match (High Priority)
            if (mb_strlen($fullPhrase) > 10) {
                $q->orWhere('question', 'LIKE', '%' . $fullPhrase . '%')
                  ->orWhere('case_text', 'LIKE', '%' . $fullPhrase . '%');
            }

            foreach ($keywords as $keyword) {
                $q->orWhere('question', 'LIKE', '%' . $keyword . '%')
                  ->orWhere('case_text', 'LIKE', '%' . $keyword . '%')
                  ->orWhere('correct_answer', 'LIKE', '%' . $keyword . '%');
            }

            // If question mentions specific articles, find judgments that mention the same
            foreach ($mentionedArticles as $article) {
                $q->orWhere('case_text', 'LIKE', '%' . $article->article_title . '%')
                  ->orWhere('correct_answer', 'LIKE', '%' . $article->article_title . '%');
            }
        });

        $tasks = $queryBuilder->limit(100)->get();

        // 4. Advanced Ranking (Hybrid Logic) & Domain Filtering
        $tasks = $tasks->map(function ($task) use ($keywords, $mentionedArticles, $fullPhrase) {
            $score = 0;
            
            // Full Phrase relevance (Extremely high if matches)
            if ($fullPhrase && $task->question && mb_stripos($task->question, $fullPhrase) !== false) $score += 100;
            if ($fullPhrase && $task->case_text && mb_stripos($task->case_text, $fullPhrase) !== false) $score += 50;

            // Keyword Relevance
            $matchedKeywords = 0;
            foreach ($keywords as $keyword) {
                $found = false;
                if ($task->question && mb_stripos($task->question, $keyword) !== false) { $score += 15; $found = true; }
                if ($task->correct_answer && mb_stripos($task->correct_answer, $keyword) !== false) { $score += 8; $found = true; }
                if ($task->case_text && mb_stripos($task->case_text, $keyword) !== false) { $score += 3; $found = true; }
                if ($found) $matchedKeywords++;
            }

            // Legal Relevance (Match by Articles)
            foreach ($mentionedArticles as $article) {
                if ($task->case_text && mb_stripos($task->case_text, $article->article_title) !== false) {
                    $score += 60;
                }
                if ($task->correct_answer && mb_stripos($task->correct_answer, $article->article_title) !== false) {
                    $score += 40;
                }
            }

            if (!empty($task->correct_answer)) $score += 25;
            
            // Domain Mismatch Penalty: If the query has family words, but the task has labor words
            $familyKeywords = ['نفقة', 'زوجة', 'طلاق', 'حضانة', 'مهر', 'خلع', 'أسرة'];
            $laborKeywords = ['عامل', 'صاحب العمل', 'مكافأة نهاية الخدمة', 'أجر', 'إجازة', 'عمالية'];
            
            $isFamilyQuery = false;
            foreach($familyKeywords as $fk) { if (mb_stripos($fullPhrase, $fk) !== false) $isFamilyQuery = true; }
            
            $isLaborTask = false;
            foreach($laborKeywords as $lk) { 
                if (($task->case_text && mb_stripos($task->case_text, $lk) !== false) || 
                    ($task->question && mb_stripos($task->question, $lk) !== false)) {
                    $isLaborTask = true;
                }
            }
            
            if ($isFamilyQuery && $isLaborTask) {
                $score -= 200; // Heavy penalty for domain mismatch
            }

            $task->relevance_score = $score;
            return $task;
        })->filter(function($task) {
            return $task->relevance_score > 30; // Minimum threshold
        })->sortByDesc('relevance_score');

        $topTasks = $tasks->take($limit);

        // 5. Direct Article Search (Always include some law articles for legal context)
        $articleLimit = 10;
        $articleQuery = LegalArticle::query();
        $articleQuery->where(function($q) use ($keywords, $fullPhrase) {
            if (mb_strlen($fullPhrase) > 10) {
                $q->orWhere('content', 'LIKE', '%' . $fullPhrase . '%');
            }
            foreach ($keywords as $keyword) {
                $q->orWhere('content', 'LIKE', '%' . $keyword . '%');
            }
        });

        $extraArticles = $articleQuery->limit($articleLimit * 3)->get()->sortByDesc(function($art) use ($keywords, $fullPhrase) {
            $score = 0;
            if ($fullPhrase && mb_stripos($art->content, $fullPhrase) !== false) $score += 150;
            
            // Prioritize Personal Status Law for family keywords
            $familyKeywords = ['نفقة', 'زوجة', 'طلاق', 'حضانة', 'مهر', 'خلع', 'أسرة', 'بنوة', 'إرث'];
            $isFamilyQuery = false;
            foreach($familyKeywords as $fk) {
                if (mb_stripos($fullPhrase, $fk) !== false) $isFamilyQuery = true;
            }
            
            if ($isFamilyQuery && mb_stripos($art->legislation_title, 'الأحوال الشخصية') !== false) {
                $score += 500; // Extreme boost to ensure it comes first
            }

            foreach ($keywords as $keyword) {
                if (mb_stripos($art->content, $keyword) !== false) $score += 20;
                if (mb_stripos($art->article_title, $keyword) !== false) $score += 50;
            }
            $art->relevance_score = $score;
            return $art;
        })->filter(function($art) {
            return $art->relevance_score > 30; // Minimum threshold for articles
        })->take($articleLimit);

        // Merge results and sort globally by relevance
        $results = collect();
        foreach ($extraArticles as $article) {
            $results->push((object)[
                'id' => $article->id,
                'question' => $article->article_title,
                'correct_answer' => $article->content,
                'case_text' => $article->content,
                'source_type' => 'article',
                'case_reference' => "مادة رقم " . $article->article_number,
                'relevance_score' => $article->relevance_score
            ]);
        }
        
        foreach ($topTasks as $task) {
            $results->push($task);
        }

        // Sort everything together so the best context wins
        $sorted = $results->sortByDesc('relevance_score');
        
        // Remove duplicate texts to prevent repetitive context
        return $sorted->unique('case_text')->take($limit)->values();
    }

    private function extractKeywords($query)
    {
        $stopWords = ['ما', 'هي', 'ماذا', 'كيف', 'هل', 'في', 'من', 'على', 'عن', 'إلى', 'مع', 'متى', 'أين', 'لماذا', 'كم', 'أي', 'قضية', 'حكم', 'نظام', 'مادة', 'أو', 'و', 'ثم'];
        $symbols = ['؟', '!', '.', ',', '،', ':', '؛', '(', ')', '[', ']', '{', '}', '"', '\'', '-'];
        
        $cleanQuery = str_replace($symbols, ' ', $query);
        $words = preg_split('/\s+/', $cleanQuery, -1, PREG_SPLIT_NO_EMPTY);
        
        $keywords = [];
        foreach ($words as $word) {
            if (mb_strlen($word) < 3 || in_array($word, $stopWords)) continue;
            
            $keywords[] = $word;
            // Enhanced Arabic Stemming: Remove Al-, Wa-, Bi-, Li-, Fa-
            $stem = preg_replace('/^(ال|ب|ل|ك|ف|و|بال|لل)/u', '', $word);
            if (mb_strlen($stem) >= 3 && $stem !== $word) {
                $keywords[] = $stem;
            }
        }
        
        return array_unique($keywords);
    }
}
