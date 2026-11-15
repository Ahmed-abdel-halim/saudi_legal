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
     * Uses compound term matching, noun verification, and relevance thresholding.
     */
    public function search(string $query, int $limit = 3): Collection
    {
        $rawQuery = trim($query);
        $normQuery = ShariaNormalizer::normalize($rawQuery);
        $substantiveKeywords = ShariaNormalizer::extractSubstantiveKeywords($rawQuery);
        $stems = ShariaNormalizer::extractStems($rawQuery);

        // If the query contains no substantive keywords, cannot match safely
        if (empty($substantiveKeywords)) {
            return collect();
        }

        $candidates = collect();

        // 1. Identify specific base stems (exclude generic action verbs)
        $genericVerbs = ['شراء', 'بيع', 'عمل', 'فعل', 'دفع', 'اخذ', 'اخذت', 'اعطاء', 'طلب'];
        $baseStems = array_values(array_filter($stems, function($s) use ($genericVerbs) {
            return !in_array($s, $genericVerbs) && !str_starts_with($s, 'ال') && !str_starts_with($s, 'بال');
        }));

        // 2. TIER 1: Compound Match (Questions matching pairs of distinct topic stems)
        if (count($baseStems) >= 2) {
            $compound = FatwaQaPair::query()
                ->with(['record', 'citations'])
                ->where(function($q) use ($baseStems) {
                    for ($i = 0; $i < count($baseStems); $i++) {
                        for ($j = $i + 1; $j < count($baseStems); $j++) {
                            $s1 = $baseStems[$i];
                            $s2 = $baseStems[$j];
                            if (mb_stripos($s1, $s2) === false && mb_stripos($s2, $s1) === false) {
                                $q->orWhere(function($sub) use ($s1, $s2) {
                                    $sub->where('question', 'LIKE', '%' . $s1 . '%')
                                        ->where('question', 'LIKE', '%' . $s2 . '%');
                                });
                            }
                        }
                    }
                })
                ->take(30)
                ->get();

            foreach ($compound as $c) {
                $candidates->push($c);
            }
        }

        // 3. TIER 2: Exact question phrase match or single substantive stems in question
        if ($candidates->count() < 25) {
            $single = FatwaQaPair::query()
                ->with(['record', 'citations'])
                ->where(function ($q) use ($rawQuery, $substantiveKeywords, $baseStems) {
                    if (mb_strlen($rawQuery) > 8) {
                        $q->where('question', 'LIKE', '%' . $rawQuery . '%');
                    }
                    $searchTerms = !empty($baseStems) ? $baseStems : $substantiveKeywords;
                    foreach ($searchTerms as $term) {
                        $q->orWhere('question', 'LIKE', '%' . $term . '%');
                    }
                })
                ->take(25)
                ->get();

            foreach ($single as $s) {
                if (!$candidates->contains('id', $s->id)) {
                    $candidates->push($s);
                }
            }
        }

        // 4. TIER 3: Search citations (Hadiths) matching key stems
        $citationMatches = ShariaCitation::query()
            ->with(['qaPair.record', 'qaPair.citations'])
            ->where(function ($q) use ($baseStems, $substantiveKeywords) {
                $terms = !empty($baseStems) ? $baseStems : $substantiveKeywords;
                foreach ($terms as $keyword) {
                    $q->orWhere('hadith_text', 'LIKE', '%' . $keyword . '%');
                }
            })
            ->limit(10)
            ->get();

        foreach ($citationMatches as $citation) {
            if ($citation->qaPair && !$candidates->contains('id', $citation->qaPair->id)) {
                $candidates->push($citation->qaPair);
            }
        }

        // 5. Strict Scoring: Requires REAL substantive topical overlap
        $scored = $candidates->map(function (FatwaQaPair $item) use ($rawQuery, $normQuery, $substantiveKeywords, $baseStems) {
            $itemQuestion = $item->question ?? '';
            $itemAnswer = $item->final_answer ?? '';
            $normQuestion = ShariaNormalizer::normalize($itemQuestion);
            $normAnswer = ShariaNormalizer::normalize($itemAnswer);

            $topicalScore = 0;
            $matchedKeywordsCount = 0;

            // A. Exact Question Phrase Match
            if (mb_stripos($itemQuestion, $rawQuery) !== false) {
                $topicalScore += 180;
            }

            // B. Substantive Question Similarity & Density
            $sim = ShariaNormalizer::similarity($rawQuery, $itemQuestion);
            if ($sim > 0.15) {
                $topicalScore += ($sim * 120);
            }
            // Keyword density bonus for concise matching questions
            $wordCount = max(1, count(explode(' ', $itemQuestion)));
            if ($wordCount <= 35) {
                $topicalScore += 25; // Bonus for direct concise questions over lengthy personal stories
            }

            // C. Special validation for "الذهب": verify it's a real noun, not 'يذهب' or 'المذهب'
            $hasGoldInQuery = in_array('ذهب', $baseStems) || in_array('الذهب', $baseStems);
            if ($hasGoldInQuery) {
                $hasRealGold = preg_match('/(\s|^)(الذهب|ذهب|ذهبا|لذهب)(\s|[،.]|$)/u', $itemQuestion) ||
                               preg_match('/(\s|^)(الذهب|ذهب|ذهبا|لذهب)(\s|[،.]|$)/u', $itemAnswer);
                if ($hasRealGold) {
                    $topicalScore += 100;
                } else {
                    $topicalScore -= 100; // Penalize false-positive 'يذهب'
                }
            }

            // D. Match substantive keywords specifically in Question (high weight) vs Answer
            foreach ($substantiveKeywords as $kw) {
                if (mb_stripos($normQuestion, $kw) !== false) {
                    $topicalScore += 40;
                    $matchedKeywordsCount++;
                } elseif (mb_stripos($normAnswer, $kw) !== false) {
                    $topicalScore += 8;
                    $matchedKeywordsCount++;
                }
            }

            // Guardrail: If no substantive keywords matched in question/answer, score is 0
            if ($topicalScore < 30 || $matchedKeywordsCount === 0) {
                $item->search_score = 0;
                return $item;
            }

            // E. Bonus for Human Verification ONLY IF topically relevant
            if ($item->traffic_light === 'green' || $item->review_status === 'Approved') {
                $topicalScore += 20;
            } elseif ($item->traffic_light === 'red' || $item->review_status === 'Rejected') {
                $topicalScore = 0;
            }

            // F. Bonus for authentic citations
            if ($item->citations && $item->citations->count() > 0) {
                $validCitations = $item->citations->filter(function($c) {
                    return !empty($c->hadith_text) || !empty($c->quran_text);
                });
                $topicalScore += min(30, $validCitations->count() * 10);
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
