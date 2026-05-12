<?php

namespace App\Console\Commands;

use App\Models\LegalArticle;
use App\Models\LegalCitation;
use App\Models\LegalQaPair;
use App\Models\LegalRecord;
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

        $totalLines = $limit ?? $this->countLines($file);
        $this->info("Starting import — {$totalLines} records expected.");

        $bar = $this->output->createProgressBar($totalLines);
        $bar->setFormat(' %current%/%max% [%bar%] %percent:3s%% | Imported: %imported% | Skipped: %skipped% | Errors: %errors%');
        $bar->setMessage(0, 'imported');
        $bar->setMessage(0, 'skipped');
        $bar->setMessage(0, 'errors');
        $bar->start();

        LazyCollection::make(function () use ($file) {
            $handle = fopen($file, 'r');
            while (($line = fgets($handle)) !== false) {
                yield trim($line);
            }
            fclose($handle);
        })
        ->filter(fn($line) => !empty($line))
        ->when($limit, fn($col) => $col->take($limit))
        ->each(function (string $line) use ($bar) {
            try {
                $data = json_decode($line, true, 512, JSON_THROW_ON_ERROR);
                $this->importRecord($data);
            } catch (\Throwable $e) {
                $this->errors++;
                $bar->setMessage($this->errors, 'errors');
                // Log first 200 chars of failed line for debugging
                $this->newLine();
                $this->error("Error: " . $e->getMessage() . " | Line: " . mb_substr($line, 0, 100));
            }

            $bar->setMessage($this->imported, 'imported');
            $bar->setMessage($this->skipped,  'skipped');
            $bar->advance();
        });

        $bar->finish();
        $this->newLine(2);
        $this->info("✅ Done! Imported: {$this->imported} | Skipped: {$this->skipped} | Errors: {$this->errors}");

        return self::SUCCESS;
    }

    // ─────────────────────────────────────────────────────────────────────────

    private function importRecord(array $data): void
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

        // ── Citations (collected & deduplicated from all qa_pairs) ────────────
        $this->importCitations($record, $data['qa_pairs'] ?? []);

        // ── Q&A Pairs ─────────────────────────────────────────────────────────
        $this->importQaPairs($record, $data['qa_pairs'] ?? [], $caseNum, $fullText);

        $this->imported++;
    }

    // ─────────────────────────────────────────────────────────────────────────

    private function importCitations(LegalRecord $record, array $qaPairs): void
    {
        $seen = []; // deduplicate within one record

        foreach ($qaPairs as $qa) {
            $articles = $qa['legal_articles'] ?? [];
            foreach ($articles as $rawCitation) {
                if (empty($rawCitation)) continue;

                [$systemName, $articleNumber] = $this->parseCitationString($rawCitation);
                $citationSource = $this->detectCitationSource($rawCitation);

                $key = mb_strtolower("{$systemName}|{$articleNumber}");
                if (isset($seen[$key])) continue;
                $seen[$key] = true;

                // Only search legal_articles for official law citations
                $legalArticleId = ($citationSource === 'law')
                    ? $this->findLegalArticleId($articleNumber, $systemName)
                    : null;

                LegalCitation::create([
                    'legal_record_id'  => $record->id,
                    'system_name'      => $systemName ?: $rawCitation,
                    'article_number'   => $articleNumber,
                    'citation_source'  => $citationSource,
                    'legal_article_id' => $legalArticleId,
                ]);
            }
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

    private function importQaPairs(LegalRecord $record, array $qaPairs, string $caseReference, string $fullText): void
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
                'reviewed_at'      => null,
            ]);

            // Create Legacy AI Task and Legal Task for workbench compatibility
            $aiTask = \App\Models\AiTask::create([
                'task_type'         => 'legal_verification',
                'original_data'     => $qa['question'] ?? '',
                'ai_suggestion'     => $qa['answer']   ?? '',
                'client_id'         => 1, // Default Admin Client
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

        // Extract article number: digits or Arabic textual numbers
        $articleNumber = null;
        if (preg_match('/المادة[^\d]*(\d+)/u', $raw, $m)) {
            $articleNumber = $m[1];
        }

        // Extract system name: text after "من" or "في" or just the whole string
        $systemName = null;
        if (preg_match('/(?:من|في|وفق|بموجب)\s+(.+)$/u', $raw, $m)) {
            $systemName = trim($m[1]);
        } elseif (! str_contains($raw, 'المادة')) {
            // The whole string is just a system name
            $systemName = $raw;
        } else {
            // Fallback: extract system name as everything after article mention
            if (preg_match('/المادة[^م]*\s+(.+)$/u', $raw, $m)) {
                $systemName = trim($m[1]);
            }
        }

        return [$systemName, $articleNumber];
    }

    /**
     * Search legal_articles for a matching article.
     */
    private function findLegalArticleId(?string $articleNumber, ?string $systemName): ?int
    {
        if (! $articleNumber && ! $systemName) return null;

        $query = LegalArticle::query();

        if ($articleNumber) {
            $query->where(function ($q) use ($articleNumber) {
                $q->where('article_title', 'LIKE', "%{$articleNumber}%")
                  ->orWhere('content',     'LIKE', "%المادة {$articleNumber}%");
            });
        }

        if ($systemName) {
            // Use key words from system name (avoid short stop words)
            $words = array_values(array_filter(
                preg_split('/\s+/u', $systemName, -1, PREG_SPLIT_NO_EMPTY),
                fn($w) => mb_strlen($w) > 3
            ));
            $topWords = array_slice($words, 0, 3);

            if (!empty($topWords)) {
                // OR-match: any keyword hit is enough to identify the system
                $query->where(function ($q) use ($topWords) {
                    foreach ($topWords as $word) {
                        $q->orWhere('legislation_title', 'LIKE', "%{$word}%");
                    }
                });
            }
        }

        return $query->value('id');
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
