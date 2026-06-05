<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\LegalArticle;

class ReportMissingCitations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'legal:missing
                            {--csv : Export the missing articles list to a CSV file}
                            {--file : Scan the master JSONL file directly instead of the database}
                            {--path=Radiif_Master_16-5-2026.jsonl : Path to the master JSONL file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Find and report laws and article numbers cited in QA pairs but missing from the database';

    // Cache for article matching to optimize DB queries
    private array $articleCache = [];

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $scanFile = $this->option('file');
        $fileName = $this->option('path') ?: 'Radiif_Master_16-5-2026.jsonl';
        $filePath = base_path($fileName);

        // If the user wants to scan the file directly
        if ($scanFile) {
            if (!file_exists($filePath)) {
                $this->error("Master dataset file not found at: {$filePath}");
                return self::FAILURE;
            }
            $this->info("Scanning raw master dataset file directly: {$fileName}...");
            $missing = $this->scanJsonlFile($filePath);
        } else {
            $this->info("Scanning database citations table (imported questions only)...");
            $missing = $this->scanDatabase();
        }

        if ($missing->isEmpty()) {
            $this->info("Great news! No missing articles were found.");
            return self::SUCCESS;
        }

        $this->info("Found " . $missing->count() . " unique missing articles across " . $missing->pluck('system_name')->unique()->count() . " laws.");

        if ($this->option('csv')) {
            $csvFile = base_path('missing_articles.csv');
            $handle = fopen($csvFile, 'w');
            // Write UTF-8 BOM for Excel Arabic support
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($handle, ['النظام / القانون', 'رقم المادة', 'عدد مرات الإحالة (التكرار)', 'نوع الحالة / التصنيف', 'التوصية / التفاصيل']);

            foreach ($missing as $row) {
                $classification = $this->classifyMissingRow($row->system_name, $row->article_number);
                fputcsv($handle, [
                    $row->system_name,
                    $row->article_number ?: 'غير محدد',
                    $row->count,
                    $classification['class'],
                    $classification['details']
                ]);
            }
            fclose($handle);
            $this->info("✅ Exported list successfully to: {$csvFile}");
            return self::SUCCESS;
        }

        // Group by system_name to display nicely in the console
        $grouped = $missing->groupBy('system_name');

        foreach ($grouped as $systemName => $rows) {
            $this->comment("\n--------------------------------------------------");
            $this->info("📍 النظام: {$systemName}");
            $this->comment("--------------------------------------------------");

            $headers = ['رقم المادة', 'عدد الإحالات'];
            $data = [];
            foreach ($rows as $row) {
                $data[] = [
                    $row->article_number ?: 'غير محدد',
                    $row->count . ' مرات'
                ];
            }
            $this->table($headers, $data);
        }

        $this->newLine();
        $this->info("💡 نصيحة: يمكنك تشغيل الأمر مع خيار تصدير ملف Excel/CSV كالتالي:");
        $this->comment("   php artisan legal:missing --csv");
        $this->comment("   php artisan legal:missing --file --csv  (لمسح الملف بالكامل)");

        return self::SUCCESS;
    }

    /**
     * Scan the database legal_citations table
     */
    private function scanDatabase()
    {
        return DB::table('legal_citations')
            ->whereNull('legal_article_id')
            ->where('citation_source', 'law')
            ->whereNotNull('system_name')
            ->select('system_name', 'article_number', DB::raw('count(*) as count'))
            ->groupBy('system_name', 'article_number')
            ->orderBy('count', 'desc')
            ->orderBy('system_name')
            ->get();
    }

    /**
     * Scan the JSONL file directly
     */
    private function scanJsonlFile(string $filePath)
    {
        $rawCitations = [];
        $handle = fopen($filePath, 'r');
        
        $this->info("Reading JSONL lines...");
        while (($line = fgets($handle)) !== false) {
            $line = trim($line);
            if (empty($line)) continue;

            $data = json_decode($line, true);
            if (!$data) continue;

            $qaPairs = $data['qa_pairs'] ?? [];
            foreach ($qaPairs as $qa) {
                $articles = $qa['legal_articles'] ?? [];
                foreach ($articles as $rawCitation) {
                    if (empty($rawCitation)) continue;

                    $citation = $this->classifyCitation($rawCitation);
                    
                    if ($citation['type'] === 'law') {
                        $systemName = $citation['system_name'] ?: 'نظام غير محدد';
                        $articleNumber = $citation['article_number'];

                        // Check if it's missing from DB
                        $articleId = $this->findLegalArticleId($articleNumber, $systemName);
                        if ($articleId === null) {
                            $key = $systemName . '|||' . ($articleNumber ?: 'none');
                            if (!isset($rawCitations[$key])) {
                                $rawCitations[$key] = [
                                    'system_name' => $systemName,
                                    'article_number' => $articleNumber,
                                    'count' => 0
                                ];
                            }
                            $rawCitations[$key]['count']++;
                        }
                    }
                }
            }
        }
        fclose($handle);

        // Convert to collection and sort by count desc
        return collect(array_values($rawCitations))
            ->map(fn($item) => (object) $item)
            ->sortByDesc('count');
    }

    /**
     * Classify citation helpers copied from ImportLegalRecords.php
     */
    private function classifyCitation(string $raw): array
    {
        $raw = preg_replace('/[\x{064B}-\x{0652}]/u', '', $raw); // Strip tashkeel/diacritics
        $raw = trim($raw);

        if (mb_strlen($raw) > 120) {
            return [
                'type'           => 'free_text',
                'system_name'    => null,
                'article_number' => null,
                'raw_text'       => $raw,
            ];
        }

        $isNonLaw = (bool) preg_match(
            '/(?:العقد|اتفاق|محضر|إفادة|تقرير|بينة|سند|فاتورة|كشف|خطاب|مبدأ|قاعدة شرعية|فقه|أسباب|منطوق|تاريخ|مستنبط|استنباط|قضائي مستقر|أحكام قضائية|الاجتهاد)/ui',
            $raw
        );

        if ($isNonLaw) {
            return [
                'type'           => 'free_text',
                'system_name'    => null,
                'article_number' => null,
                'raw_text'       => $raw,
            ];
        }

        [$systemName, $articleNumber] = $this->parseCitationString($raw);

        $hasLawIndicator = (bool) preg_match(
            '/(?:من|في|بموجب|وفق)\s+(?:نظام|لائحة|قانون|مرسوم|قرار)|^(?:نظام|لائحة|قانون)\s+/ui',
            $raw
        );

        $hasArticle = $articleNumber !== null || (bool) preg_match('/مادة|المادة/ui', $raw);

        if ($hasArticle || $hasLawIndicator) {
            return [
                'type'           => 'law',
                'system_name'    => $systemName ?: 'نظام غير محدد',
                'article_number' => $articleNumber,
                'raw_text'       => $raw,
            ];
        }

        return [
            'type'           => 'free_text',
            'system_name'    => null,
            'article_number' => null,
            'raw_text'       => $raw,
        ];
    }

    private function parseCitationString(string $raw): array
    {
        $rawClean = trim($this->convertArabicNumbers($raw));
        
        // Preprocess 'م' followed by a number or slash + number
        // Examples: "م 26" -> "المادة 26", "م/20" -> "المادة 20", "م 26 فقرة 3" -> "المادة 26 فقرة 3"
        $rawClean = preg_replace('/(?<!\p{L})م\s*[\/\-]?\s*\(?(\d+)\)?(?!\p{L})/u', 'المادة $1', $rawClean);
        
        // Handle case: "نظام المحاكم التجارية (1/24)" -> "نظام المحاكم التجارية المادة 24"
        $rawClean = preg_replace('/(?<!\d)(?<!\d\/)\(?(\d+)\/(\d+)\)?(?!\/\d)(?!\d)/u', 'المادة $2', $rawClean);

        // Normalize parentheses around "المادة" number
        $rawClean = preg_replace('/(?:المادة|مادة)\s*(?:رقم\s*)?\(\s*([^)]+)\s*\)/ui', 'المادة $1', $rawClean);

        $articleNumber = null;
        $systemName = null;

        // 1. Try to find the first digits after "المادة"
        if (preg_match('/المادة\s+(?:رقم\s+)?(\d+)/ui', $rawClean, $m)) {
            $articleNumber = $m[1];
        } else {
            // 2. Try to find written Arabic words after "المادة"
            if (preg_match('/المادة\s+(.*?)(?:\s+(?:من|في|بموجب|وفق)|$)/ui', $rawClean, $m)) {
                $written = trim($m[1]);
                $articleNumber = $this->parseWrittenArabicNumber($written);
            }
        }

        // Now extract the system name:
        // Case A: "المادة X من/في [System]"
        if (preg_match('/المادة\s+.+?\s+(?:من|في|وفق|بموجب)\s+(.+)$/ui', $rawClean, $m)) {
            $systemName = trim($m[1]);
        }
        // Case B: "الفقرة X من المادة Y من/في [System]"
        elseif (preg_match('/من\s+المادة\s+.+?\s+(?:من|في|وفق|بموجب)\s+(.+)$/ui', $rawClean, $m)) {
            $systemName = trim($m[1]);
        }
        // Case C: "[System] المادة X"
        elseif (preg_match('/^(.+?)\s+المادة\s+/ui', $rawClean, $m)) {
            $systemName = trim($m[1]);
        }
        // Case D: No "المادة" word at all
        elseif (!preg_match('/المادة/ui', $rawClean)) {
            $systemName = $rawClean;
        }

        // Clean up system name from prefixes/suffixes and details
        if ($systemName) {
            // Clean up prefix details like "الفصل السابع من", "الباب التاسع من", "الفقرة الأولى من", "بند من"
            $systemName = preg_replace('/^(?:الفصل|الباب|الفقرة|البند)\s+(?:[^\s]+)\s+(?:من\s+)?/ui', '', $systemName);
            $systemName = preg_replace('/^(?:من\s+)/ui', '', $systemName);
            
            // Strip suffix details like "الصادرة بقرار...", "المشار إليها...", "التي تنص...", "في مراعاة..."
            $systemName = preg_replace('/\s+(?:و?الصادر|و?الصادرة|و?المعدل|و?المعدلة|و?المشار|و?التي|و?الذي|و?الخاص|وبند|رقم\s+\d+|بتاريخ|عام\s+\d+|لعام\s+\d+|\(|\||,\s*|في\s+مراعاة).*$/ui', '', $systemName);
            
            $systemName = trim($systemName, " \t\n\r\0\x0B().|-");
        }

        return [$systemName, $articleNumber];
    }

    private function parseWrittenArabicNumber(string $text): ?string
    {
        $text = trim($text);
        $map = [
            'الأولى' => '1', 'الأول' => '1',
            'الثانية' => '2', 'الثاني' => '2',
            'الثالثة' => '3', 'الثالث' => '3',
            'الرابعة' => '4', 'الرابع' => '4',
            'الخامسة' => '5', 'الخامس' => '5',
            'السادسة' => '6', 'السادس' => '6',
            'السابعة' => '7', 'السابع' => '7',
            'الثامنة' => '8', 'الثامن' => '8',
            'التاسعة' => '9', 'التاسع' => '9',
            'العاشرة' => '10', 'العاشر' => '10',
            'الحادية عشرة' => '11', 'الحادي عشر' => '11',
            'الثانية عشرة' => '12', 'الثاني عشر' => '12',
            'الثالثة عشرة' => '13', 'الثالث عشر' => '13',
            'الرابعة عشرة' => '14', 'الرابع عشر' => '14',
            'الخامسة عشرة' => '15', 'الخامس عشر' => '15',
            'السادسة عشرة' => '16', 'السادس عشر' => '16', 'السادسة عشر' => '16',
            'السابعة عشرة' => '17', 'السابع عشر' => '17', 'السابعة عشر' => '17',
            'الثامنة عشرة' => '18', 'الثامن عشر' => '18', 'الثامنة عشر' => '18',
            'التاسعة عشرة' => '19', 'التاسع عشر' => '19', 'التاسعة عشر' => '19',
            'العشرون' => '20', 'العشرين' => '20',
            'الحادية والعشرون' => '21', 'الحادي والعشرون' => '21', 'الحادية والعشرين' => '21',
            'الثانية والعشرون' => '22', 'الثاني والعشرون' => '22', 'الثانية والعشرين' => '22',
            'الثالثة والعشرون' => '23', 'الثالث والعشرون' => '23', 'الثالثة والعشرين' => '23',
            'الرابعة والعشرون' => '24', 'الرابع والعشرون' => '24', 'الرابعة والعشرين' => '24',
            'الخامسة والعشرون' => '25', 'الخامس والعشرون' => '25', 'الخامسة والعشرين' => '25',
            'السادسة والعشرون' => '26', 'السادس والعشرون' => '26', 'السادسة والعشرين' => '26',
            'السابعة والعشرون' => '27', 'السابع والعشرون' => '27', 'السابعة والعشرين' => '27',
            'الثامنة والعشرون' => '28', 'الثامن والعشرون' => '28', 'الثامنة والعشرين' => '28',
            'التاسعة والعشرون' => '29', 'التاسع والعشرون' => '29', 'التاسعة والعشرين' => '29',
            'الثلاثون' => '30', 'الثلاثين' => '30',
            'الحادية والثلاثون' => '31', 'الحادية والثلاثين' => '31',
            'الثانية والثلاثون' => '32', 'الثانية والثلاثين' => '32',
            'الخمسون' => '50', 'الخمسين' => '50',
            'الحادية والخمسون' => '51', 'الحادية والخمسين' => '51',
            'الخامسة والخمسون' => '55', 'الخامسة والخمسين' => '55',
            'السادسة والخمسون' => '56', 'السادسة والخمسين' => '56',
            'الثامنة والخمسون' => '58', 'الثامنة والخمسين' => '58',
            'السبعون' => '70', 'السبعين' => '70',
            'الثالثة والسبعون' => '73', 'الثالثة والسبعين' => '73',
            'السادسة والسبعون' => '76', 'السادسة والسبعين' => '76',
            'الثامنة والسبعون' => '78', 'الثامنة والسبعين' => '78',
            'التسعون' => '90', 'التسعين' => '90',
            'العاشرة بعد المائة' => '110',
            'الرابعة والستون بعد المائة' => '164', 'الرابعة والستين بعد المائة' => '164',
            'الثانية عشرة بعد المائتين' => '212', 'الثانية عشرة بعد المائتان' => '212'
        ];

        uksort($map, fn($a, $b) => mb_strlen($b) <=> mb_strlen($a));

        foreach ($map as $word => $num) {
            if (str_contains($text, $word)) {
                return $num;
            }
        }

        return null;
    }

    private function findLegalArticleId(?string $articleNumber, ?string $systemName): ?int
    {
        if (!$articleNumber) {
            return null;
        }

        $systemName = trim($systemName);
        if (empty($systemName)) {
            return null;
        }

        $isNonLaw = preg_match('/(?:العقد|اتفاق|محضر|إفادة|تقرير|بينة|سند|فاتورة|كشف|خطاب|مبدأ|قاعدة شرعية|فقه|أسباب|منطوق|تاريخ|مستنبط|استنباط|قضائي مستقر)/ui', $systemName);
        if ($isNonLaw) {
            return null;
        }

        $cacheKey = $systemName . '|||' . $articleNumber;
        if (array_key_exists($cacheKey, $this->articleCache)) {
            return $this->articleCache[$cacheKey];
        }

        $query = LegalArticle::query();

        $synonyms = [
            'الإثبات'            => ['نظام الإثبات', 'قانون الإثبات'],
            'المحاكم التجارية'   => ['نظام المحاكم التجارية'],
            'نظام المحاكم التجارية' => ['المحاكم التجارية'],
            'المرافعات الشرعية'  => ['نظام المرافعات الشرعية'],
            'نظام المرافعات الشرعية' => ['المرافعات الشرعية'],
            'المعاملات المدنية'  => ['نظام المعاملات المدنية'],
            'نظام المعاملات المدنية' => ['المعاملات المدنية'],
            'الشركات'            => ['نظام الشركات'],
            'نظام الشركات'       => ['الشركات'],
            'التحكيم'            => ['نظام التحكيم'],
            'نظام التحكيم'       => ['التحكيم'],
            'العمل'              => ['نظام العمل'],
            'نظام العمل'         => ['العمل'],
        ];

        $searchTerms = [$systemName];
        foreach ($synonyms as $key => $names) {
            if (str_contains($systemName, $key)) {
                $searchTerms = array_merge($searchTerms, $names);
            }
        }

        $query->where(function($q) use ($searchTerms) {
            foreach ($searchTerms as $term) {
                $q->orWhere('legislation_title', $term)
                  ->orWhere('legislation_title', 'LIKE', "%{$term}%");
            }
        });

        $textNum = is_numeric($articleNumber) ? $this->arabicOrdinal((int) $articleNumber) : null;

        $query->where(function($q) use ($articleNumber, $textNum) {
            $exactTitles = [];
            $exactTitles[] = "المادة {$articleNumber}";
            $exactTitles[] = "المادة ({$articleNumber})";
            $exactTitles[] = "المادة {$articleNumber}:";
            $exactTitles[] = "المادة ({$articleNumber}):";
            $exactTitles[] = "المادة {$articleNumber} مكرر";
            $exactTitles[] = "المادة ({$articleNumber}) مكرر";
            
            if ($textNum) {
                $exactTitles[] = "المادة {$textNum}";
                $exactTitles[] = "المادة ({$textNum})";
                $exactTitles[] = "المادة {$textNum}:";
                $exactTitles[] = "المادة ({$textNum}):";
                $exactTitles[] = "المادة {$textNum} مكرر";
            }

            $q->whereIn('article_title', $exactTitles);
            
            foreach ($exactTitles as $title) {
                $q->orWhere('article_title', 'LIKE', $title);
            }

        });

        $articles = $query->get();
        $matchedArticle = null;
        
        foreach ($articles as $art) {
            $title = trim($art->article_title);
            if ($textNum && ($title === "المادة {$textNum}" || $title === "المادة ({$textNum})")) {
                $matchedArticle = $art;
                break;
            }
            if ($title === "المادة {$articleNumber}" || $title === "المادة ({$articleNumber})") {
                $matchedArticle = $art;
                break;
            }
        }

        if (!$matchedArticle && $articles->isNotEmpty()) {
            foreach ($articles as $art) {
                $title = trim($art->article_title);
                if ($textNum && $articleNumber % 10 === 0) {
                    if (str_contains($title, 'و' . $textNum)) {
                        continue;
                    }
                }
                $matchedArticle = $art;
                break;
            }
        }

        $this->articleCache[$cacheKey] = $matchedArticle?->id;
        return $this->articleCache[$cacheKey];
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
            if ($one === 1) return 'الحادية و' . $tens[$ten];
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
        $text = preg_replace('/[\x{064B}-\x{0652}]/u', '', $text); // Strip tashkeel/diacritics
        return mb_strtolower(trim($text));
    }

    private function classifyMissingRow(string $systemName, ?string $articleNumber): array
    {
        $cleanSystem = trim($systemName);
        
        // 1. Detect if it's a contract or private document
        $isContract = (bool) preg_match(
            '/(?:العقد|اتفاق|محضر|إفادة|تقرير|بينة|سند|فاتورة|كشف|خطاب|مبدأ|شراكة|تأسيس)/ui',
            $cleanSystem
        );
        if ($isContract) {
            return [
                'class' => 'عقد / وثيقة خاصة',
                'details' => 'عقود أو وثائق خاصة بالشركاء أو القضية، وليست أنظمة رسمية.'
            ];
        }
        
        // 2. Check if the system name is "نظام غير محدد"
        if ($cleanSystem === 'نظام غير محدد') {
            return [
                'class' => 'نظام غير محدد',
                'details' => 'الإحالة لم تذكر اسم النظام بوضوح (مثال: "وفقاً للمادة العاشرة").'
            ];
        }

        $normalizedSystem = $this->normalizeArabic($cleanSystem);
        
        $synonyms = [
            'الإثبات'            => 'نظام الإثبات',
            'المحاكم التجارية'   => 'نظام المحاكم التجارية',
            'المرافعات الشرعية'  => 'نظام المرافعات الشرعية',
            'المعاملات المدنية'  => 'نظام المعاملات المدنية',
            'الشركات'            => 'نظام الشركات',
            'التحكيم'            => 'نظام التحكيم',
            'العمل'              => 'نظام العمل',
        ];

        $matchedLegislation = null;
        foreach ($synonyms as $keyword => $fullName) {
            if (mb_strpos($normalizedSystem, $this->normalizeArabic($keyword)) !== false) {
                $matchedLegislation = $fullName;
                break;
            }
        }

        if ($matchedLegislation) {
            if ($articleNumber && is_numeric($articleNumber)) {
                $textNum = $this->arabicOrdinal((int) $articleNumber);
                $exactTitles = [
                    "المادة {$articleNumber}",
                    "المادة ({$articleNumber})",
                    "المادة {$articleNumber}:",
                    "المادة ({$articleNumber}):",
                    "المادة {$articleNumber} مكرر",
                    "المادة ({$articleNumber}) مكرر",
                ];
                if ($textNum) {
                    $exactTitles[] = "المادة {$textNum}";
                    $exactTitles[] = "المادة ({$textNum})";
                    $exactTitles[] = "المادة {$textNum}:";
                    $exactTitles[] = "المادة ({$textNum}):";
                    $exactTitles[] = "المادة {$textNum} مكرر";
                    $exactTitles[] = "المادة ({$textNum}) مكرر";
                }

                $exists = \App\Models\LegalArticle::where('legislation_title', $matchedLegislation)
                    ->whereIn('article_title', $exactTitles)
                    ->exists();

                if ($exists) {
                    return [
                        'class' => 'موجود بالفعل (خطأ في صياغة الإحالة)',
                        'details' => "المادة متوفرة تحت نظام [{$matchedLegislation}]. يرجى تعديل الإحالة في السؤال لتطابق الرسمية."
                    ];
                } else {
                    return [
                        'class' => 'مادة مفقودة في نظام موجود',
                        'details' => "النظام [{$matchedLegislation}] موجود، ولكن المادة رقم [{$articleNumber}] غير متوفرة بقاعدة البيانات."
                    ];
                }
            }
            
            // Try parsing article number from written form if number is null or "غير حدد"
            $parsedNum = null;
            if (preg_match('/الماد[ةة]\s+(.*?)(?:\s+(?:من|في|بموجب|وفق)|$)/ui', $normalizedSystem, $m)) {
                $written = trim($m[1]);
                $parsedNum = $this->parseWrittenArabicNumber($written);
            }

            if ($parsedNum) {
                $textNum = $this->arabicOrdinal((int) $parsedNum);
                $exactTitles = [
                    "المادة {$parsedNum}",
                    "المادة ({$parsedNum})",
                    "المادة {$parsedNum}:",
                    "المادة ({$parsedNum}):",
                    "المادة {$parsedNum} مكرر",
                    "المادة ({$parsedNum}) مكرر",
                ];
                if ($textNum) {
                    $exactTitles[] = "المادة {$textNum}";
                    $exactTitles[] = "المادة ({$textNum})";
                    $exactTitles[] = "المادة {$textNum}:";
                    $exactTitles[] = "المادة ({$textNum}):";
                    $exactTitles[] = "المادة {$textNum} مكرر";
                    $exactTitles[] = "المادة ({$textNum}) مكرر";
                }

                $exists = \App\Models\LegalArticle::where('legislation_title', $matchedLegislation)
                    ->whereIn('article_title', $exactTitles)
                    ->exists();

                if ($exists) {
                    return [
                        'class' => 'موجود بالفعل (خطأ في صياغة الإحالة)',
                        'details' => "المادة متوفرة تحت نظام [{$matchedLegislation}]. يرجى تعديل الإحالة في السؤال لتطابق الرسمية."
                    ];
                }
            }
            
            return [
                'class' => 'مادة غير محددة أو مفقودة في نظام موجود',
                'details' => "النظام [{$matchedLegislation}] موجود، ولكن لم يتم التعرف على المادة أو أنها مفقودة."
            ];
        }

        return [
            'class' => 'نظام مفقود بالكامل',
            'details' => 'هذا النظام (أو اللائحة) غير مدرج بالكامل في قاعدة البيانات حالياً.'
        ];
    }
}
