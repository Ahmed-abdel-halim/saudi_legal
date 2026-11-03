<?php

namespace App\Services\Legal;

use App\Models\LegalTask;
use App\Models\LegalArticle;
use Illuminate\Support\Facades\DB;

class LegalSearchService
{
    /**
     * قائمة الكلمات الشائعة والحروف التي لا تحمل دلالة موضوعية قانونية خاصة
     * وتُستبعد لمنع تلويث استعلامات الـ SQL
     */
    protected array $stopWords = [
        'ما', 'هي', 'ماذا', 'كيف', 'هل', 'في', 'من', 'على', 'عن', 'إلى', 'مع', 'متى', 'أين', 'لماذا', 'كم', 'أي',
        'قضية', 'حكم', 'نظام', 'مادة', 'أو', 'و', 'ثم', 'حيث', 'أن', 'إن', 'التي', 'الذي', 'الذين', 'هذا', 'هذه',
        'ذلك', 'تلك', 'كان', 'يكون', 'تكون', 'بناء', 'وفقا', 'وفق', 'طبقا', 'رقم', 'قرار', 'سنة', 'عام', 'يوم',
        'شهر', 'مدة', 'دون', 'عدم', 'كل', 'جميع', 'أحد', 'آخر', 'أخرى', 'بين', 'طرف', 'طرفين', 'حق', 'طلب', 'أمر',
        'إجراء', 'إجراءات'
    ];

    /**
     * محرك البحث الهجين العام والشامل لكافة الأنظمة السعودية (قضايا + مواد نظامية)
     */
    public function search($query, $limit = 5)
    {
        // 1. التوسيع الدلالي الشامل للاستعلام واستخراج المفاهيم والأنظمة المستهدفة
        $expansionService = new LegalQueryExpansionService();
        $expansion = $expansionService->expand($query);

        $expandedQuery = $expansion['expanded_query'];
        $legalTerms    = $expansion['legal_terms'];
        $targetSystem  = $expansion['target_system'];

        // كشف تلقائي احتياطي للنظام المستهدف إذا لم يُكتشف صراحةً
        if (!$targetSystem) {
            if (preg_match('/(تجاري|تجارية|تاجر|تجار|محاكم تجارية)/u', $query)) {
                $targetSystem = 'نظام المحاكم التجارية';
            } elseif (preg_match('/(نكاح|طلاق|خلع|زوج|زوجة|حضانة|نفقة|إرث|تركة)/u', $query)) {
                $targetSystem = 'نظام الأحوال الشخصية';
            } elseif (preg_match('/(عقد|عقود|شرط جزائي|عربون|بيع ملك الغير|التزام|معاملات مدنية)/u', $query)) {
                $targetSystem = 'نظام المعاملات المدنية';
            } elseif (preg_match('/(عامل|عمل|وظيفة|راتب|أجر|فصل تعسفي|خدمة)/u', $query)) {
                $targetSystem = 'نظام العمل';
            } elseif (preg_match('/(تنفيذ|سند تنفيذي|شيك|كمبيالة|سند لأمر)/u', $query)) {
                $targetSystem = 'نظام التنفيذ';
            } elseif (preg_match('/(تستر|تستر تجاري)/u', $query)) {
                $targetSystem = 'نظام مكافحة التستر';
            } elseif (preg_match('/(شركة|شركات|مدير شركة|مساهمة|مسؤولية محدودة)/u', $query)) {
                $targetSystem = 'نظام الشركات';
            }
        }

        // 2. استخراج الكلمات المفتاحية النقية
        $keywords = $this->extractKeywords($query);
        $fullPhrase = trim($query);

        // 3. استخراج المواد المذكورة صراحة في السؤال
        $refService = new LegalReferenceService();
        $mentionedArticles = $refService->getMentionedArticles($query);
        if ($mentionedArticles->isEmpty() && $expandedQuery !== $query) {
            $mentionedArticles = $refService->getMentionedArticles($expandedQuery);
        }

        // 4. استرجاع وتصنيف المواد النظامية (Legal Articles)
        $articles = collect();

        // أ. إذا تم تحديد نظام محدد مستهدف، استرجع مواده أولاً
        if ($targetSystem) {
            $sysArticles = LegalArticle::where('legislation_title', 'LIKE', "%{$targetSystem}%")
                ->where(function($q) use ($keywords, $fullPhrase, $legalTerms) {
                    if (mb_strlen($fullPhrase) > 8) {
                        $q->orWhere('content', 'LIKE', '%' . $fullPhrase . '%');
                    }
                    foreach ($legalTerms as $term) {
                        if (mb_strlen($term) >= 4) {
                            $q->orWhere('content', 'LIKE', '%' . $term . '%')
                              ->orWhere('article_title', 'LIKE', '%' . $term . '%');
                        }
                    }
                    foreach ($keywords as $kw) {
                        if (mb_strlen($kw) >= 3) {
                            $q->orWhere('content', 'LIKE', '%' . $kw . '%')
                              ->orWhere('article_title', 'LIKE', '%' . $kw . '%');
                        }
                    }
                })
                ->limit(30)
                ->get();

            $articles = $articles->merge($sysArticles);
        }

        // ب. البحث في نصوص الأنظمة واللوائح السعودية العامة عن المفاهيم القانونية
        $searchTerms = array_filter(array_merge([$fullPhrase], $legalTerms), fn($t) => mb_strlen($t) >= 4);

        if (!empty($searchTerms)) {
            $conceptArticles = LegalArticle::where(function($q) {
                    $q->where('legislation_title', 'LIKE', 'نظام%')
                      ->orWhere('legislation_title', 'LIKE', 'اللائحة%');
                })
                ->where('legislation_title', 'NOT LIKE', 'تنظيم%')
                ->where('legislation_title', 'NOT LIKE', 'الترتيبات%')
                ->where('legislation_title', 'NOT LIKE', '%الأوسمة%')
                ->where('legislation_title', 'NOT LIKE', '%الري%')
                ->where(function($q) use ($searchTerms) {
                    foreach (array_slice($searchTerms, 0, 6) as $term) {
                        $q->orWhere('content', 'LIKE', '%' . $term . '%')
                          ->orWhere('article_title', 'LIKE', '%' . $term . '%');
                    }
                })
                ->limit(35)
                ->get();

            $articles = $articles->merge($conceptArticles)->unique('id');
        }

        // ج. تقييم ورتبة المواد النظامية
        $rankedArticles = $articles->map(function($art) use ($keywords, $legalTerms, $targetSystem, $fullPhrase) {
            $score = 0;
            if ($targetSystem && mb_stripos($art->legislation_title, $targetSystem) !== false) {
                $score += 150;
            }
            if ($fullPhrase && mb_stripos($art->content, $fullPhrase) !== false) {
                $score += 180;
            }
            foreach ($legalTerms as $term) {
                if (mb_stripos($art->content, $term) !== false) $score += 60;
                if (mb_stripos($art->article_title, $term) !== false) $score += 80;
            }
            $matchedKw = 0;
            foreach ($keywords as $kw) {
                if (mb_stripos($art->content, $kw) !== false) { $score += 20; $matchedKw++; }
                if (mb_stripos($art->article_title, $kw) !== false) { $score += 35; }
            }
            if ($matchedKw >= 2) {
                $score += ($matchedKw * 25);
            }

            return (object)[
                'id'              => $art->id,
                'question'        => $art->article_title,
                'correct_answer'  => $art->content,
                'case_text'       => $art->content,
                'source_type'     => 'article',
                'case_reference'  => "مادة رقم " . $art->article_number,
                'law_system_name' => $art->legislation_title ?? '',
                'relevance_score' => $score
            ];
        })->filter(fn($a) => $a->relevance_score > 35)->sortByDesc('relevance_score');

        // 5. استرجاع وتصنيف السوابق والقضايا (Legal Tasks & Judgments)
        $taskQuery = LegalTask::query();
        $taskQuery->where(function($q) use ($keywords, $fullPhrase, $legalTerms, $mentionedArticles) {
            if (mb_strlen($fullPhrase) > 10) {
                $q->orWhere('question', 'LIKE', '%' . $fullPhrase . '%');
            }
            foreach ($legalTerms as $term) {
                if (mb_strlen($term) >= 4) {
                    $q->orWhere('question', 'LIKE', '%' . $term . '%')
                      ->orWhere('correct_answer', 'LIKE', '%' . $term . '%');
                }
            }
            foreach (array_slice($keywords, 0, 4) as $kw) {
                $q->orWhere('question', 'LIKE', '%' . $kw . '%')
                  ->orWhere('correct_answer', 'LIKE', '%' . $kw . '%');
            }
            foreach ($mentionedArticles as $mArt) {
                $q->orWhere('case_text', 'LIKE', '%' . $mArt->article_title . '%');
            }
        });

        $tasks = $taskQuery->limit(40)->get();

        $rankedTasks = $tasks->map(function($t) use ($keywords, $legalTerms, $fullPhrase, $targetSystem, $mentionedArticles) {
            $score = 0;
            $text = ($t->question ?? '') . ' ' . ($t->correct_answer ?? '') . ' ' . ($t->case_text ?? '');

            if ($fullPhrase && mb_stripos($text, $fullPhrase) !== false) $score += 100;
            if ($targetSystem && mb_stripos($t->correct_law_system ?? '', $targetSystem) !== false) $score += 80;

            foreach ($legalTerms as $term) {
                if (mb_stripos($text, $term) !== false) $score += 40;
            }
            foreach ($keywords as $kw) {
                if (mb_stripos($t->question ?? '', $kw) !== false) $score += 25;
                if (mb_stripos($t->correct_answer ?? '', $kw) !== false) $score += 15;
            }
            foreach ($mentionedArticles as $mArt) {
                if (mb_stripos($text, $mArt->article_title) !== false) $score += 50;
            }
            if (!empty($t->correct_answer)) $score += 20;

            $t->relevance_score = $score;
            $t->source_type     = $t->source_type ?? 'judgment';
            $t->law_system_name = $t->correct_law_system ?? 'سابقة قضائية';
            return $t;
        })->filter(fn($t) => $t->relevance_score > 35)->sortByDesc('relevance_score');

        // 6. دمج المواد والسوابق بترتيب الرتبة الكلي وإزالة التكرار
        $merged = $rankedArticles->merge($rankedTasks)->sortByDesc('relevance_score');

        return $merged->unique(function($item) {
            $txt = $item->case_text ?? $item->correct_answer ?? '';
            return md5(trim($txt));
        })->take($limit)->values();
    }

    /**
     * استخراج الكلمات المفتاحية الدقيقة مع إزالة حروف العطف والجر وأل التعريف
     */
    private function extractKeywords($query)
    {
        $symbols = ['؟', '!', '.', ',', '،', ':', '؛', '(', ')', '[', ']', '{', '}', '"', '\'', '-'];
        $cleanQuery = str_replace($symbols, ' ', $query);
        $words = preg_split('/\s+/', $cleanQuery, -1, PREG_SPLIT_NO_EMPTY);

        $keywords = [];
        foreach ($words as $word) {
            if (mb_strlen($word) < 3 || in_array($word, $this->stopWords)) continue;

            // إزالة أحرف الزيادة من بداية الكلمة (ال، و، ب، ل، ف)
            $stem = preg_replace('/^(ال|ب|ل|ك|ف|و|بال|لل)/u', '', $word);
            if (mb_strlen($stem) >= 3 && !in_array($stem, $this->stopWords)) {
                $keywords[] = $stem;
            } else {
                $keywords[] = $word;
            }
        }

        $unique = array_values(array_unique($keywords));

        usort($unique, function($a, $b) {
            return mb_strlen($b) <=> mb_strlen($a);
        });

        return array_slice($unique, 0, 15);
    }
}
