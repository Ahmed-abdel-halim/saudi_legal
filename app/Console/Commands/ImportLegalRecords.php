<?php

namespace App\Console\Commands;

use App\Models\LegalArticle;
use App\Models\LegalCitation;
use App\Models\LegalQaPair;
use App\Models\LegalRecord;
use App\Models\User;
use App\Models\AiTask;
use App\Models\LegalTask;
use App\Models\ClientQuestion;
use App\Services\LegalReferenceService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\LazyCollection;

class ImportLegalRecords extends Command
{
    protected $signature = 'legal:import
                            {file? : Path to the JSONL file (default: Radiif_Golden_5k_Abstract_QA_Fixed.jsonl)}
                            {--fresh : Drop and re-import all records}
                            {--limit= : Only import N records (for testing)}';

    protected $description = 'Import legal cases from JSONL into legal_records, legal_citations, legal_qa_pairs';

    // Maps court_type (Arabic) → sub_domain (English)
    private array $courtTypeMap = [
        'تجارية'        => 'Commercial Law',
        'عمالية'        => 'Labor Law',
        'عمالي'         => 'Labor Law',
        'جزائية'        => 'Criminal Law',
        'جزائي'         => 'Criminal Law',
        'إدارية'        => 'Administrative Law',
        'إداري'         => 'Administrative Law',
        'الأحوال الشخصية' => 'Personal Status Law',
        'أحوال'         => 'Personal Status Law',
        'مدنية'         => 'Civil Law',
        'عامة'          => 'General Law',
    ];

    private LegalReferenceService $refService;
    private int $imported = 0;
    private int $skipped  = 0;
    private int $errors   = 0;

    public function __construct(LegalReferenceService $refService)
    {
        parent::__construct();
        $this->refService = $refService;
    }

    public function handle(): int
    {
        ini_set('memory_limit', '-1');
        set_time_limit(0);

        $file  = $this->argument('file') ?? base_path('Radiif_Golden_5k_Abstract_QA_Fixed.jsonl');
        $limit = $this->option('limit') ? (int) $this->option('limit') : null;

        if (! file_exists($file)) {
            $this->error("File not found: {$file}");
            return self::FAILURE;
        }

        if ($this->option('fresh')) {
            $this->warn('--fresh: Truncating legal_qa_pairs, legal_citations, legal_records ...');
            DB::statement('SET FOREIGN_KEY_CHECKS=0');
            LegalQaPair::truncate();
            LegalCitation::truncate();
            LegalRecord::truncate();
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
            $this->info('Tables cleared.');
        }

        // Prevent memory leaks from query logging during massive inserts
        \Illuminate\Support\Facades\DB::disableQueryLog();

        $totalLines = $limit ?? $this->countLines($file);
        $this->info("Starting import — {$totalLines} records expected.");

        $bar = $this->output->createProgressBar($totalLines);
        $bar->setFormat(' %current%/%max% [%bar%] %percent:3s%% | Imported: %imported% | Skipped: %skipped% | Errors: %errors%');
        $bar->setMessage(0, 'imported');
        $bar->setMessage(0, 'skipped');
        $bar->setMessage(0, 'errors');
        $bar->start();

        // Find a valid client/admin user to own these tasks (cached once)
        $clientId = User::where('role', 'client')->first()?->id 
                    ?? User::where('role', 'admin')->first()?->id 
                    ?? User::first()?->id;

        LazyCollection::make(function () use ($file) {
            $handle = fopen($file, 'r');
            while (($line = fgets($handle)) !== false) {
                yield trim($line);
            }
            fclose($handle);
        })
        ->filter(fn($line) => !empty($line))
        ->when($limit, fn($col) => $col->take($limit))
        ->each(function (string $line) use ($bar, $clientId) {
            try {
                $data = json_decode($line, true, 512, JSON_THROW_ON_ERROR);
                $this->importRecord($data, $clientId);
                unset($data);
            } catch (\Throwable $e) {
                $this->errors++;
                $bar->setMessage($this->errors, 'errors');
                // Log first 100 chars of failed line for debugging (safe fallback)
                $this->newLine();
                $safeLine = substr($line, 0, 100);
                $this->error("Error: " . $e->getMessage() . " | Line: " . $safeLine);
            }

            $bar->setMessage($this->imported, 'imported');
            $bar->setMessage($this->skipped,  'skipped');
            $bar->advance();

            // Force garbage collection to prevent OOM crashes on massive JSONL
            gc_collect_cycles();
        });

        $bar->finish();
        $this->newLine(2);
        $this->info("✅ Done! Imported: {$this->imported} | Skipped: {$this->skipped} | Errors: {$this->errors}");

        return self::SUCCESS;
    }

    // ─────────────────────────────────────────────────────────────────────────

    private function importRecord(array $data, ?int $clientId): void
    {
        $meta      = $data['metadata'] ?? [];
        $caseNum   = trim($meta['case_number'] ?? '');
        $courtType = trim($meta['court_type']  ?? '');
        $date      = trim($meta['date']        ?? '');

        // Skip if already imported
        $recordId = $this->buildRecordId($caseNum);
        if (LegalRecord::where('record_id', $recordId)->exists()) {
            $this->skipped++;
            return;
        }

        $fullText = $this->stripHtml($data['full_case_text'] ?? '');

        $record = LegalRecord::create([
            'record_id'        => $recordId,
            'domain'           => 'Legal',
            'sub_domain'       => $this->resolveSubDomain($courtType),
            'language'         => 'ar',
            'upload_date'      => $this->parseDate($date),
            'tags'             => $data['tags'] ?? [],
            'source_type'      => 'Court_Judgment',
            'source_reference' => $caseNum,
            'court_type'       => $courtType,
            'full_text'        => $fullText,
            'case_summary'     => $data['case_summary'] ?? null,
        ]);

        // ── Citations & Q&A Pairs ───────────────────────────────────────────
        $this->importQaPairs($record, $data['qa_pairs'] ?? [], $caseNum, $fullText, $clientId);

        $this->imported++;
    }

    // ─────────────────────────────────────────────────────────────────────────

    private function importCitations(LegalRecord $record, LegalQaPair $qaPair, array $articles): void
    {
        foreach ($articles as $rawCitation) {
            if (empty($rawCitation)) continue;

            [$systemName, $articleNumber] = $this->parseCitationString($rawCitation);
            $citationSource = $this->detectCitationSource($rawCitation);

            // Only search legal_articles for official law citations
            $legalArticleId = ($citationSource === 'law')
                ? $this->findLegalArticleId($articleNumber, $systemName)
                : null;

            LegalCitation::create([
                'legal_record_id'  => $record->id,
                'legal_qa_pair_id' => $qaPair->id,
                'system_name'      => $systemName ?: $rawCitation,
                'article_number'   => $articleNumber,
                'citation_source'  => $citationSource,
                'legal_article_id' => $legalArticleId,
            ]);
        }
    }

    /**
     * Detect whether a citation string refers to:
     *   - 'religious' → Quranic verse / Hadith / Fiqh book reference
     *   - 'law'       → official Saudi law / regulation / ministerial order
     *   - 'contract'  → clause from a contract, agreement, policy, or annex
     *   - 'other'     → unrecognised reference
     */
    private function detectCitationSource(string $raw): string
    {
        $normalized = $this->normalizeArabic($raw);

        // ── RELIGIOUS indicators (checked first — highest priority) ────────────
        $religiousKeywords = [
            'قوله تعالى', 'قال تعالى', 'قال الله', 'بسم الله',
            'قال النبي', 'قال رسول الله', 'الحديث', 'الآية', 'سورة',
            'تبصرة الحكام', 'المغني', 'الفقه', 'ابن فرحون',
            'ابن قدامة', 'الكافي', 'المقنع', 'الانصاف',
        ];
        foreach ($religiousKeywords as $kw) {
            if (str_contains($normalized, $this->normalizeArabic($kw))) {
                return 'religious';
            }
        }

        // ── CONTRACT indicators ───────────────────────────────────────────────
        $contractKeywords = [
            'العقد', 'الاتفاقية', 'الاتفاق', 'الوثيقة',
            'البوليصة', 'الملحق', 'الوثيقه', 'البوليصه',
            'بنود العقد', 'شروط العقد', 'من العقد', 'من الاتفاقية',
        ];
        foreach ($contractKeywords as $kw) {
            if (str_contains($normalized, $this->normalizeArabic($kw))) {
                return 'contract';
            }
        }

        // ── LAW indicators ────────────────────────────────────────────────────
        $lawKeywords = [
            'نظام', 'لائحة', 'لائحه', 'مرسوم', 'قرار',
            'دليل اجرائي', 'الاجراءات', 'قانون', 'تشريع',
        ];
        foreach ($lawKeywords as $kw) {
            if (str_contains($normalized, $this->normalizeArabic($kw))) {
                return 'law';
            }
        }

        return 'other';
    }

    private function importQaPairs(LegalRecord $record, array $qaPairs, string $caseReference, string $fullText, ?int $clientId = null): void
    {
        foreach ($qaPairs as $i => $qa) {
            $num  = str_pad($i + 1, 3, '0', STR_PAD_LEFT); // 001, 002 ...
            $qaId = "Q-{$num}";

            $qaPair = LegalQaPair::create([
                'legal_record_id'  => $record->id,
                'qa_id'            => $qaId,
                'question'         => $qa['question']        ?? '',
                'generated_answer' => $qa['answer']          ?? '',
                'review_status'    => 'Pending',
                'reviewer_id'      => null,
                'corrected_answer' => null,
            ]);

            // ── Import Citations specifically for THIS QA Pair ──────────────
            $this->importCitations($record, $qaPair, $qa['legal_articles'] ?? []);

            // Create Legacy AI Task and Legal Task for workbench compatibility
            $aiTask = \App\Models\AiTask::create([
                'task_type'         => 'legal_verification',
                'original_data'     => $qa['question'] ?? '',
                'ai_suggestion'     => $qa['answer']   ?? '',
                'client_id'         => $clientId,
                'status'            => 'pending',
                'consensus_status'  => 'pending',
                'required_responses'=> 3,
                'task_domain'       => 'law',
                'allow_all_roles'   => true
            ]);

            // Create LegalTask (Linking table)
            \App\Models\LegalTask::create([
                'task_id'            => $aiTask->id,
                'source_type'        => 'legal_qa_pair',
                'source_id'          => $qaPair->id,
                'task_type'          => 'verification',
                'status'             => 'pending',
                'question'           => $qa['question'] ?? '',
                'proposed_answer'    => $qa['answer']   ?? '',
                'case_text'          => $fullText,
                'case_reference'     => $caseReference,
                'domain'             => 'law',
                'source_file'        => 'Radiif_Golden_5k_Abstract_QA_Fixed.jsonl',
            ]);
        }
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    /**
     * Parse a citation string like:
     *   "المادة (30) من نظام المحاكم التجارية"
     *   "المادة التاسعة والعشرون من نظام الإثبات"
     *   "نظام المحاكم التجارية"   ← no article number
     *
     * Returns [systemName, articleNumber]
     */
    private function parseCitationString(string $raw): array
    {
        $raw = $this->convertArabicNumbers($raw);
        $raw = $this->normalizeWrittenNumbers($raw);

        $articleNumber = null;
        if (preg_match('/المادة[^\d]*(\d+)/u', $raw, $m)) {
            $articleNumber = $m[1];
        }

        $systemName = null;
        // Logic: Extract system name after "من" or "في"
        if (preg_match('/(?:من|في|وفق|بموجب)\s+(.+)$/u', $raw, $m)) {
            $systemName = trim($m[1]);
        } elseif (! str_contains($raw, 'المادة')) {
            $systemName = $raw;
        }

        return [$systemName, $articleNumber];
    }

    private function normalizeWrittenNumbers(string $text): string
    {
        $map = [
            'الحادية والثلاثون' => '31', 'الثانية والثلاثون' => '32',
            'الحادية والعشرون' => '21', 'الثانية والعشرون' => '22', 'الثالثة والعشرون' => '23', 'الرابعة والعشرون' => '24', 'الخامسة والعشرون' => '25', 'السادسة والعشرون' => '26', 'السابعة والعشرون' => '27', 'الثامنة والعشرون' => '28', 'التاسعة والعشرون' => '29',
            'الحادية عشرة' => '11', 'الثانية عشرة' => '12', 'الثالثة عشرة' => '13', 'الرابعة عشرة' => '14', 'الخامسة عشرة' => '15', 'السادسة عشرة' => '16', 'السابعة عشرة' => '17', 'الثامنة عشرة' => '18', 'التاسعة عشرة' => '19',
            'الأولى' => '1', 'الثانية' => '2', 'الثالثة' => '3', 'الرابعة' => '4', 'الخامسة' => '5', 'السادسة' => '6', 'السابعة' => '7', 'الثامنة' => '8', 'التاسعة' => '9', 'العاشرة' => '10', 'العشرون' => '20', 'الثلاثون' => '30'
        ];

        // Sort by length descending to catch "التاسعة والعشرون" before "التاسعة"
        uksort($map, fn($a, $b) => mb_strlen($b) <=> mb_strlen($a));

        foreach ($map as $word => $digit) {
            if (str_contains($text, $word)) {
                $text = str_replace($word, $digit, $text);
            }
        }
        return $text;
    }

    /**
     * Search legal_articles for a matching article.
     */
    private function findLegalArticleId(?string $articleNumber, ?string $systemName): ?int
    {
        if (! $articleNumber && ! $systemName) return null;

        $query = LegalArticle::query();

        if ($systemName) {
            // Common Synonyms Map
            $synonyms = [
                'الإثبات' => ['نظام الإثبات', 'قانون الإثبات'],
                'المحاكم التجارية' => ['نظام المحاكم التجارية'],
                'المرافعات الشرعية' => ['نظام المرافعات الشرعية'],
                'المعاملات المدنية' => ['نظام المعاملات المدنية'],
            ];

            $searchTerms = [$systemName];
            foreach ($synonyms as $key => $names) {
                if (str_contains($systemName, $key)) {
                    $searchTerms = array_merge($searchTerms, $names);
                }
            }

            // Priority 1: Match legislation title
            $query->where(function($q) use ($searchTerms) {
                foreach ($searchTerms as $term) {
                    $q->orWhere('legislation_title', 'LIKE', "%{$term}%");
                }
            });
        }

        if ($articleNumber) {
            // Get the proper Arabic ordinal (e.g. 29 -> التاسعة والعشرون)
            $textNum = is_numeric($articleNumber) ? $this->arabicOrdinal((int) $articleNumber) : null;

            $query->where(function($q) use ($articleNumber, $textNum) {
                $q->where('article_title', 'LIKE', "%{$articleNumber}%")
                  ->orWhere('content', 'LIKE', "%المادة {$articleNumber}%");
                
                if ($textNum) {
                    $q->orWhere('article_title', 'LIKE', "%{$textNum}%")
                      ->orWhere('content', 'LIKE', "%المادة {$textNum}%");
                }
            });
        }

        $article = $query->first();

        // Fallback: search in content if not found by title
        if (!$article && $systemName) {
            $cleaned = str_replace(['نظام', 'لائحة', 'قانون'], '', $systemName);
            $article = LegalArticle::where('content', 'LIKE', "%{$cleaned}%")
                ->when($articleNumber, fn($q) => $q->where('content', 'LIKE', "%المادة {$articleNumber}%"))
                ->first();
        }

        return $article?->id;
    }

    private function buildRecordId(string $caseNumber): string
    {
        // Deterministic: hash case number to consistent short ID
        $hash = substr(md5($caseNumber), 0, 8);
        return 'RD-LGL-' . strtoupper($hash);
    }

    private function resolveSubDomain(string $courtType): string
    {
        foreach ($this->courtTypeMap as $keyword => $subDomain) {
            if (str_contains($courtType, $keyword)) {
                return $subDomain;
            }
        }
        return 'General Law';
    }

    private function arabicOrdinal(int $number): string
    {
        $ones = [
            1 => 'الأولى', 2 => 'الثانية', 3 => 'الثالثة', 4 => 'الرابعة', 5 => 'الخامسة',
            6 => 'السادسة', 7 => 'السابعة', 8 => 'الثامنة', 9 => 'التاسعة', 10 => 'العاشرة',
            11 => 'الحادية عشرة', 12 => 'الثانية عشرة', 13 => 'الثالثة عشرة', 14 => 'الرابعة عشرة',
            15 => 'الخامسة عشرة', 16 => 'السادسة عشرة', 17 => 'السابعة عشرة', 18 => 'الثامنة عشرة', 19 => 'التاسعة عشرة'
        ];
        $tens = [
            20 => 'العشرون', 30 => 'الثلاثون', 40 => 'الأربعون', 50 => 'الخمسون',
            60 => 'الستون', 70 => 'السبعون', 80 => 'الثمانون', 90 => 'التسعون'
        ];

        if ($number <= 19) return $ones[$number] ?? '';
        
        if ($number < 100) {
            $ten = (int) floor($number / 10) * 10;
            $one = $number % 10;
            if ($one === 0) return $tens[$ten];
            if ($one === 1) return 'الحادية و' . $tens[$ten]; // 21, 31, 41...
            return $ones[$one] . ' و' . $tens[$ten];
        }

        if ($number === 100) return 'المائة';
        if ($number < 200) {
            return $this->arabicOrdinal($number - 100) . ' بعد المائة';
        }

        if ($number === 200) return 'المائتين';
        if ($number < 300) {
            return $this->arabicOrdinal($number - 200) . ' بعد المائتين';
        }

        if ($number === 300) return 'الثلاثمائة';
        if ($number < 400) {
            return $this->arabicOrdinal($number - 300) . ' بعد الثلاثمائة';
        }

        return (string) $number;
    }

    private function parseDate(string $date): ?string
    {
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return $date; // already YYYY-MM-DD (Hijri stored as-is)
        }
        return null;
    }

    private function stripHtml(string $html): string
    {
        // Replace <br> variants with newline before stripping
        $html = preg_replace('/<br\s*\/?>/i', "\n", $html);
        $html = preg_replace('/<\/p>/i', "\n\n", $html);
        return trim(strip_tags($html));
    }

    private function convertArabicNumbers(string $str): string
    {
        return str_replace(
            ['٠','١','٢','٣','٤','٥','٦','٧','٨','٩'],
            ['0','1','2','3','4','5','6','7','8','9'],
            $str
        );
    }

    private function normalizeArabic(string $text): string
    {
        $text = preg_replace('/[أإآ]/u', 'ا', $text);
        $text = str_replace(['ة','ى'], ['ه','ي'], $text);
        return mb_strtolower(trim($text));
    }

    private function countLines(string $file): int
    {
        $lines = 0;
        $handle = fopen($file, 'r');
        while (fgets($handle) !== false) $lines++;
        fclose($handle);
        return $lines;
    }
}
