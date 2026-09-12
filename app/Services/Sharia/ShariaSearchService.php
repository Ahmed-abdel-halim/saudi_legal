<?php

namespace App\Services\Sharia;

use App\Models\FatwaQaPair;
use App\Models\ShariaRecord;
use App\Models\ShariaCitation;
use Illuminate\Support\Collection;

class ShariaSearchService
{
    /**
     * Perform Hybrid Search across Sharia QA Pairs, Records, and Citations.
     * Uses strict substantive keyword matching and relevance thresholding to prevent false positives.
     */
    public function search(string $query, int $limit = 3): Collection
    {
        $rawQuery = trim($query);
        $substantiveKeywords = ShariaNormalizer::extractSubstantiveKeywords($rawQuery);

        // If the query contains no substantive keywords, cannot match safely
        if (empty($substantiveKeywords)) {
            return collect();
        }

        // 1. Fetch candidates matching substantive keywords or the exact phrase
        $qaBuilder = FatwaQaPair::query()
            ->with(['record', 'citations'])
            ->where(function ($q) use ($rawQuery, $substantiveKeywords) {
                // Exact full phrase match
                if (mb_strlen($rawQuery) > 8) {
                    $q->where('question', 'LIKE', '%' . $rawQuery . '%');
                }

                // Substantive keyword matches
                foreach ($substantiveKeywords as $keyword) {
                    $q->orWhere('question', 'LIKE', '%' . $keyword . '%')
                      ->orWhere('generated_answer', 'LIKE', '%' . $keyword . '%')
                      ->orWhere('corrected_answer', 'LIKE', '%' . $keyword . '%');
                }
            });

        $candidates = $qaBuilder->limit(30)->get();

        // 2. Search citations (Quran verses & Hadiths) matching substantive keywords
        $citationMatches = ShariaCitation::query()
            ->with(['qaPair.record', 'qaPair.citations'])
            ->where(function ($q) use ($substantiveKeywords) {
                foreach ($substantiveKeywords as $keyword) {
                    $q->orWhere('quran_text', 'LIKE', '%' . $keyword . '%')
                      ->orWhere('hadith_text', 'LIKE', '%' . $keyword . '%');
                }
            })
            ->limit(10)
            ->get();

        foreach ($citationMatches as $citation) {
            if ($citation->qaPair && !$candidates->contains('id', $citation->qaPair->id)) {
                $candidates->push($citation->qaPair);
            }
        }

        // 3. Strict Scoring: Requires REAL substantive topical overlap
        $scored = $candidates->map(function (FatwaQaPair $item) use ($rawQuery, $substantiveKeywords) {
            $itemQuestion = $item->question ?? '';
            $itemAnswer = $item->final_answer ?? '';
            $normQuestion = ShariaNormalizer::normalize($itemQuestion);
            $normAnswer = ShariaNormalizer::normalize($itemAnswer);

            $topicalScore = 0;
            $matchedKeywordsCount = 0;

            // A. Exact Question Phrase Match
            if (mb_stripos($itemQuestion, $rawQuery) !== false) {
                $topicalScore += 150;
            }

            // B. Substantive Question Similarity
            $sim = ShariaNormalizer::similarity($rawQuery, $itemQuestion);
            if ($sim > 0.25) {
                $topicalScore += ($sim * 100);
            }

            // C. Match substantive keywords specifically in the Question
            foreach ($substantiveKeywords as $kw) {
                if (mb_stripos($normQuestion, $kw) !== false) {
                    $topicalScore += 30;
                    $matchedKeywordsCount++;
                } elseif (mb_stripos($normAnswer, $kw) !== false) {
                    $topicalScore += 8;
                    $matchedKeywordsCount++;
                }
            }

            // Guardrail: If no substantive keywords matched in question/answer, score is 0
            if ($topicalScore < 25 || $matchedKeywordsCount === 0) {
                $item->search_score = 0;
                return $item;
            }

            // D. Bonus for Human Verification ONLY IF topically relevant
            if ($item->traffic_light === 'green' || $item->review_status === 'Approved') {
                $topicalScore += 20;
            } elseif ($item->traffic_light === 'red' || $item->review_status === 'Rejected') {
                $topicalScore = 0;
            }

            // E. Bonus for authentic citations ONLY IF topically relevant
            if ($item->citations && $item->citations->count() > 0) {
                $topicalScore += min(20, $item->citations->count() * 4);
            }

            $item->search_score = $topicalScore;
            return $item;
        });

        // Filter out irrelevant records (score must be at least 45 to be considered a genuine match)
        return $scored->filter(fn($item) => $item->search_score >= 45)
                      ->sortByDesc('search_score')
                      ->take($limit)
                      ->values();
    }
}
