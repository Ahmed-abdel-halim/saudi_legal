<?php

namespace App\Services;

use App\Models\LegalArticle;
use App\Models\LegalCitation;
use App\Models\LegalQaPair;
use App\Models\LegalRecord;

class LegalReferenceService
{
    /**
     * ════════════════════════════════════════════════════════════════
     *  EXISTING API  (used by older parts of the app — unchanged)
     * ════════════════════════════════════════════════════════════════
     */

    /**
     * Main entry point to get articles from any text
     */
    public function getMentionedArticles(string $text, $fallbackSystem = null, $fallbackArticleNum = null)
    {
        $text = $this->convertArabicNumbers($text);
        $references = $this->extractReferences($text);

        $articles = collect();
        foreach ($references as $ref) {
            $systemToUse = $ref['system'] ?: $fallbackSystem;
            $article = $this->findArticle($ref['number'], $systemToUse);
            if ($article) $articles->push($article);
        }

        // Fallback to task specific article if text extraction yielded nothing
        if ($articles->isEmpty() && $fallbackArticleNum && $fallbackArticleNum !== 'غير محدد') {
            $num = $this->convertArabicNumbers($fallbackArticleNum);
            $article = $this->findArticle($num, $fallbackSystem);
            if ($article) $articles->push($article);
        }

        return $articles->unique('id');
    }

    /**
     * ════════════════════════════════════════════════════════════════
     *  NEW API  (uses legal_records / legal_citations / legal_qa_pairs)
     * ════════════════════════════════════════════════════════════════
     */

    /**
     * Get a full record (with citations and QA) as the structured JSON format.
     *
     * @param  string  $recordId  e.g. "RD-LGL-A1B2C3D4"
     */
    public function getRecord(string $recordId): ?array
    {
        $record = LegalRecord::where('record_id', $recordId)
            ->with(['citations.article', 'qaPairs'])
            ->first();

        return $record ? $this->formatRecord($record) : null;
    }

    /**
     * Search QA pairs by question text across all records.
     * Returns paginated results (10 per page).
     */
    public function searchQA(string $query, int $perPage = 10)
    {
        return LegalQaPair::where('question', 'LIKE', "%{$query}%")
            ->orWhere('generated_answer', 'LIKE', "%{$query}%")
            ->with('record:id,record_id,sub_domain,source_reference')
            ->paginate($perPage);
    }

    /**
     * Get all pending QA pairs for human review (optionally filtered by sub_domain).
     */
    public function getPendingReviews(string $subDomain = null, int $perPage = 20)
    {
        return LegalQaPair::pending()
            ->when($subDomain, fn($q) => $q->whereHas(
                'record', fn($r) => $r->where('sub_domain', $subDomain)
            ))
            ->with('record:id,record_id,sub_domain,source_reference,court_type')
            ->paginate($perPage);
    }

    /**
     * Approve / Reject / Modify a QA pair (human review action).
     *
     * @param  int     $qaPairId
     * @param  string  $status          Approved | Rejected | Modified
     * @param  int     $reviewerId      User ID of the expert
     * @param  string|null $corrected   Required when status = Modified
     */
    public function reviewQaPair(
        int $qaPairId,
        string $status,
        int $reviewerId,
        ?string $corrected = null
    ): LegalQaPair {
        $qa = LegalQaPair::findOrFail($qaPairId);

        $qa->update([
            'review_status'    => $status,
            'reviewer_id'      => $reviewerId,
            'corrected_answer' => ($status === 'Modified') ? $corrected : null,
            'reviewed_at'      => now(),
        ]);

        return $qa->fresh();
    }

    /**
     * Get citations for a record — with article text from legal_articles.
     * Returns only law citations with linked article text.
     *
     * @param  string  $recordId
     */
    public function getRecordCitations(string $recordId): array
    {
        $record = LegalRecord::where('record_id', $recordId)->first();
        if (! $record) return [];

        return LegalCitation::where('legal_record_id', $record->id)
            ->with('article')
            ->get()
            ->map(fn($c) => $c->toApiArray())
            ->toArray();
    }

    /**
     * Find records that cite a specific law article.
     *
     * @param  int  $legalArticleId   ID from legal_articles table
     */
    public function getRecordsByCitation(int $legalArticleId, int $limit = 10)
    {
        return LegalCitation::where('legal_article_id', $legalArticleId)
            ->with('record:id,record_id,sub_domain,source_reference,case_summary')
            ->limit($limit)
            ->get()
            ->pluck('record');
    }

    /**
     * Statistics summary for the dashboard.
     */
    public function getStats(): array
    {
        return [
            'total_records'       => LegalRecord::count(),
            'total_qa_pairs'      => LegalQaPair::count(),
            'pending_review'      => LegalQaPair::pending()->count(),
            'approved'            => LegalQaPair::approved()->count(),
            'total_citations'     => LegalCitation::count(),
            'linked_citations'    => LegalCitation::whereNotNull('legal_article_id')->count(),
            'sub_domains'         => LegalRecord::selectRaw('sub_domain, count(*) as cnt')
                                        ->groupBy('sub_domain')
                                        ->orderByDesc('cnt')
                                        ->pluck('cnt', 'sub_domain'),
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  PRIVATE  (existing helpers — unchanged)
    // ─────────────────────────────────────────────────────────────────────────

    private function extractReferences(string $text)
    {
        $normalizedText = $this->normalizeTextualNumbers($text);
        $matches = [];
        $lastDetectedSystem = null;

        // Pattern for "Material X" or "Materials X, Y, Z"
        $articlePattern = '/(?:المادة|المواد|للمادة|للمواد|بالمادة|بالمواد|مادته|مادتها|موادها)[\s:]+([\d\(\)\s،و\-]+)/u';

        if (preg_match_all($articlePattern, $normalizedText, $found, PREG_SET_ORDER)) {
            foreach ($found as $match) {
                $numbersString = $match[1];
                $numbers = $this->splitNumbers($numbersString);

                $pos = mb_strpos($normalizedText, $match[0]);
                $context = mb_substr($normalizedText, $pos, 350);

                $system = $this->detectSystem($context);

                // Handle "Same System" references
                if (!$system && preg_match('/(ذات النظام|النظام المذكور|نفس النظام|النظام نفسه|منه|نظامه)/u', $context)) {
                    $system = $lastDetectedSystem;
                }

                // Check context BEFORE the mention
                if (!$system) {
                    $preContext = mb_substr($normalizedText, max(0, $pos - 300), 300);
                    $system = $this->detectSystem($preContext);
                }

                if ($system) $lastDetectedSystem = $system;

                foreach ($numbers as $num) {
                    $matches[] = ['number' => $num, 'system' => $system ?: $lastDetectedSystem];
                }
            }
        }

        return $matches;
    }

    private function detectSystem($text)
    {
        $norm = $this->normalizeArabic($text);

        // Core Saudi Laws Mapping
        $map = [
            'اثبات'    => 'نظام الإثبات',
            'مرافعات'  => 'نظام المرافعات الشرعية',
            'تجاريه'   => 'نظام المحاكم التجارية',
            'مدنيه'    => 'نظام المعاملات المدنية',
            'معاملات'  => 'نظام المعاملات المدنية',
            'شركات'    => 'نظام الشركات',
            'تحكيم'    => 'نظام التحكيم',
            'تنفيذ'    => 'نظام التنفيذ',
            'عمل'      => 'نظام العمل',
            'عمالي'    => 'نظام العمل',
            'جزائيه'   => 'نظام الإجراءات الجزائية',
            'عقوبات'   => 'نظام العقوبات',
            'مظالم'    => 'نظام ديوان المظالم',
            'افلاس'    => 'نظام الإفلاس',
            'تامينات'  => 'نظام التأمينات الاجتماعية',
            'جمارك'    => 'نظام الجمارك',
            'ضريبه'    => 'نظام الضريبة',
        ];

        foreach ($map as $key => $fullName) {
            if (str_contains($norm, $key)) {
                if (str_contains($norm, 'لائحه') || str_contains($norm, 'تنفيذيه')) {
                    return "اللائحة التنفيذية ل" . $fullName;
                }
                return $fullName;
            }
        }

        return null;
    }

    private function findArticle($number, $systemName = null)
    {
        if (empty($number)) return null;
        $query = LegalArticle::query();

        $arabicText    = $this->numberToArabicText($number);
        $arabicTextAlt = str_replace('ون', 'ين', $arabicText);

        $query->where(function($q) use ($number, $arabicText, $arabicTextAlt) {
            $q->where('article_title', 'LIKE', "% {$number}%")
              ->orWhere('article_title', 'LIKE', "%({$number})%")
              ->orWhere('article_title', 'LIKE', "%{$number} %")
              ->orWhere('article_title', 'LIKE', "%{$arabicText}%")
              ->orWhere('article_title', 'LIKE', "%{$arabicTextAlt}%");
        });

        if ($systemName) {
            $norm = $this->normalizeArabic($systemName);
            $keywords = preg_split('/\s+/', $norm, -1, PREG_SPLIT_NO_EMPTY);
            $query->where(function($q) use ($keywords, $norm) {
                $mainKeywords = array_filter($keywords, function($w) { return mb_strlen($w) > 3; });
                foreach ($mainKeywords as $word) {
                    $q->where('legislation_title', 'LIKE', "%{$word}%");
                }
                if (str_contains($norm, 'اثبات')) $q->orWhere('legislation_title', 'LIKE', '%إثبات%');
            });
        }

        $res = $query->first();

        if (!$res && $systemName) {
            $fallback = LegalArticle::query();
            $norm = $this->normalizeArabic($systemName);
            if (str_contains($norm, 'اثبات')) $fallback->where('legislation_title', 'LIKE', '%إثبات%')->orWhere('legislation_title', 'LIKE', '%اثبات%');
            elseif (str_contains($norm, 'تجاريه')) $fallback->where('legislation_title', 'LIKE', '%تجارية%');

            return $fallback->where(function($q) use ($number) {
                    $q->where('content', 'LIKE', "%المادة {$number}%")
                      ->orWhere('content', 'LIKE', "%المادة ({$number})%");
                })->first();
        }

        return $res;
    }

    // ── Format helpers ────────────────────────────────────────────────────────

    private function formatRecord(LegalRecord $record): array
    {
        return [
            'record_id' => $record->record_id,
            'metadata'  => [
                'domain'      => $record->domain,
                'sub_domain'  => $record->sub_domain,
                'language'    => $record->language,
                'upload_date' => $record->upload_date?->toDateString(),
                'tags'        => $record->tags ?? [],
            ],
            'context'   => [
                'source_type'      => $record->source_type,
                'source_reference' => $record->source_reference,
                'full_text'        => $record->full_text,
            ],
            'citations' => $record->citations->map(fn($c) => $c->toApiArray())->values()->toArray(),
            'qa_pairs'  => $record->qaPairs->map(fn($q) => $q->toApiArray())->values()->toArray(),
        ];
    }

    // ── String helpers (unchanged) ────────────────────────────────────────────

    private function normalizeArabic($text)
    {
        if (empty($text)) return '';
        $text = preg_replace('/[أإآ]/u', 'ا', $text);
        $text = str_replace(['ة','ى'], ['ه','ي'], $text);
        return mb_strtolower(trim($text));
    }

    private function splitNumbers($str)
    {
        $clean = str_replace(['و', '،', ',', '(', ')'], ' ', $str);
        return array_unique(preg_split('/\s+/', $clean, -1, PREG_SPLIT_NO_EMPTY));
    }

    private function normalizeTextualNumbers($text)
    {
        $map = [
            'الأولى' => '1', 'الثانية' => '2', 'الثالثة' => '3', 'الرابعة' => '4', 'الخامسة' => '5',
            'السادسة' => '6', 'السابعة' => '7', 'الثامنة' => '8', 'التاسعة' => '9', 'العاشرة' => '10',
            'الحادية عشرة' => '11', 'الثانية عشرة' => '12', 'الثالثة عشرة' => '13',
            'العشرون' => '20', 'العشرين' => '20', 'الثلاثون' => '30', 'الثلاثين' => '30',
            'التسعين' => '90', 'التسعون' => '90',
            'الحادية والتسعين' => '91', 'الحادية والتسعون' => '91',
            'الثانية والتسعين' => '92', 'الثانية والتسعون' => '92',
            'السابعة والتسعين' => '97', 'السابعة والتسعون' => '97',
            'الثامنة والتسعين' => '98', 'الثامنة والتسعون' => '98',
            'المائة' => '100', 'الواحدة بعد المائة' => '101', 'الرابعة بعد المائة' => '164',
        ];
        foreach ($map as $word => $digit) { $text = str_replace($word, $digit, $text); }
        return $text;
    }

    private function convertArabicNumbers($str)
    {
        $arabic = ['٠','١','٢','٣','٤','٥','٦','٧','٨','٩'];
        $latin  = ['0','1','2','3','4','5','6','7','8','9'];
        return str_replace($arabic, $latin, $str);
    }

    private function numberToArabicText($n)
    {
        $map = [
            1 => 'الأولى', 2 => 'الثانية', 10 => 'العاشرة',
            92 => 'الثانية والتسعون', 97 => 'السابعة والتسعون', 98 => 'الثامنة والتسعون',
            164 => 'الرابعة بعد المائة',
        ];
        return $map[(int)$n] ?? (string)$n;
    }
}
