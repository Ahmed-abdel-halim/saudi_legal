<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\LegalRecord;
use App\Models\LegalQaPair;
use App\Models\LegalCitation;
use App\Models\AiTask;
use App\Models\LegalTask;
use App\Models\User;
use App\Models\LegalArticle;
use App\Services\LegalReferenceService;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\DB;

class ImportMixedDataset extends Command
{
    protected $signature = 'legal:import-mix
                            {--fresh : Drop and re-import all mixed records}
                            {--normal-limit=400 : Number of normal questions to import}';

    protected $description = 'Import 400 normal questions mixed with 100 gold standard questions to test lawyers';

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

    public function handle(LegalReferenceService $refService): int
    {
        ini_set('memory_limit', '-1');
        set_time_limit(0);

        // تحويل ترميز قاعدة البيانات والجداول بشكل منفصل لتجنب انهيار العملية
        $tablesToConvert = ['legal_tasks', 'legal_records', 'legal_qa_pairs', 'ai_tasks_v2', 'ai_responses_v2'];
        
        try {
            $dbName = DB::connection()->getDatabaseName();
            DB::statement("ALTER DATABASE `{$dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            $this->info("Database '{$dbName}' character set converted to utf8mb4.");
        } catch (\Throwable $e) {
            $this->warn("Failed to convert database character set: " . $e->getMessage());
        }

        foreach ($tablesToConvert as $table) {
            try {
                DB::statement("ALTER TABLE `{$table}` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                $this->info("Table '{$table}' converted to utf8mb4 successfully.");
            } catch (\Throwable $e) {
                $this->warn("Failed to convert table '{$table}': " . $e->getMessage());
            }
        }

        try {
            DB::statement("SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci");
            $this->info("Connection session encoding set to utf8mb4_unicode_ci.");
        } catch (\Throwable $e) {
            $this->warn("Failed to set session encoding: " . $e->getMessage());
        }

        // تعديل نوع عمود case_text في جدول legal_tasks إلى LONGTEXT لتجنب مشاكل القطع (truncation) والترميز
        try {
            DB::statement("ALTER TABLE `legal_tasks` MODIFY `case_text` LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL");
            $this->info("Column 'case_text' in table 'legal_tasks' modified to LONGTEXT successfully.");
        } catch (\Throwable $e) {
            $this->warn("Failed to modify column 'case_text': " . $e->getMessage());
        }

        $jsonlFile = base_path('Radiif_Master_16-5-2026.jsonl');
        $xlsxFile = base_path('Radiif_Smart_Golden_100.xlsx');
        $normalLimit = (int) $this->option('normal-limit');

        if (!file_exists($jsonlFile)) {
            $this->error("JSONL file not found: {$jsonlFile}");
            return self::FAILURE;
        }

        if (!file_exists($xlsxFile)) {
            $this->error("Excel file not found: {$xlsxFile}");
            return self::FAILURE;
        }

        if ($this->option('fresh')) {
            $this->warn('Clearing existing legal records...');
            DB::statement('SET FOREIGN_KEY_CHECKS=0');
            LegalQaPair::truncate();
            LegalCitation::truncate();
            LegalRecord::truncate();
            AiTask::where('is_gold_standard', true)->delete();
            LegalTask::truncate();
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
            $this->info('Tables cleared.');
        }

        $clientId = User::where('role', 'client')->first()?->id 
                    ?? User::where('role', 'admin')->first()?->id 
                    ?? User::first()?->id;

        // 1. قراءة الـ 100 سؤال الذهبي من الـ Excel أولاً لتجهيز عملية المطابقة
        $this->info("جاري تحميل أسئلة الاختبار من ملف Excel...");
        $goldRecords = [];
        $goldLookup = [];
        
        try {
            $spreadsheet = IOFactory::load($xlsxFile);
            $sheet = $spreadsheet->getActiveSheet();
            $xlsxRows = $sheet->toArray();
            
            $headers = array_map(fn($v) => trim(mb_strtolower((string)$v)), $xlsxRows[0]);
            array_shift($xlsxRows); // حذف الترويسة

            $colMap = [
                'question' => $this->findColumnIndex($headers, ['سؤال', 'question', 'السؤال', 'النص', 'الطلب']),
                'proposed_answer' => $this->findColumnIndex($headers, ['خاطئ', 'غلط', 'مقترح', 'الذكاء', 'incorrect', 'proposed', 'wrong', 'الخطأ']),
                'correct_answer' => $this->findColumnIndex($headers, ['صحيح', 'معدل', 'الذهبي', 'correct', 'gold', 'right', 'النموذجية', 'الإجابة النموذجية', 'answer']),
                'system_name' => $this->findColumnIndex($headers, ['نظام', 'اسم النظام', 'قانون', 'law', 'system']),
                'article_number' => $this->findColumnIndex($headers, ['مادة', 'رقم المادة', 'المادة', 'article']),
                'case_reference' => $this->findColumnIndex($headers, ['مرجع', 'رقم القضية', 'reference']),
                'case_text' => $this->findColumnIndex($headers, ['الوقائع', 'نص القضية', 'حكم', 'judgment', 'text'])
            ];

            foreach ($xlsxRows as $row) {
                $q = $colMap['question'] !== null ? trim((string)($row[$colMap['question']] ?? '')) : '';
                if (empty($q)) continue;

                $caseRef = $colMap['case_reference'] !== null ? trim((string)($row[$colMap['case_reference']] ?? '')) : '';
                $normQ = $this->normalizeArabic($q);

                $goldIndex = count($goldRecords);
                $goldRecords[$goldIndex] = [
                    'question' => $q,
                    'proposed_answer' => $colMap['proposed_answer'] !== null ? trim((string)($row[$colMap['proposed_answer']] ?? '')) : 'إجابة مقترحة خاطئة للاختبار',
                    'correct_answer' => $colMap['correct_answer'] !== null ? trim((string)($row[$colMap['correct_answer']] ?? '')) : 'الإجابة الصحيحة',
                    'system_name' => $colMap['system_name'] !== null ? trim((string)($row[$colMap['system_name']] ?? '')) : 'نظام عام',
                    'article_number' => $colMap['article_number'] !== null ? trim((string)($row[$colMap['article_number']] ?? '')) : null,
                    'case_reference' => $caseRef,
                    'case_text' => $colMap['case_text'] !== null ? trim((string)($row[$colMap['case_text']] ?? '')) : $q,
                    'matched_record_data' => null,
                    'matched_qa' => null,
                ];

                if (!empty($caseRef)) {
                    $goldLookup[trim($caseRef)][$normQ] = $goldIndex;
                }
            }
        } catch (\Exception $e) {
            $this->error("فشل قراءة ملف Excel: " . $e->getMessage());
            return self::FAILURE;
        }
        $this->info("تم تحميل " . count($goldRecords) . " سؤال اختبار من Excel بنجاح.");

        // 2. قراءة ملف الماستر للأسئلة العادية ومطابقة أسئلة الاختبار
        $this->info("جاري فحص ملف الماستر لاستخراج الأسئلة العادية ومطابقة أسئلة الاختبار...");
        $normalQuestions = [];
        $totalQuestionsLoaded = 0;
        $totalGoldMatched = 0;
        $totalGoldNeeded = count($goldRecords);
        $seenRecordIds = [];

        $handle = fopen($jsonlFile, 'r');
        while (($line = fgets($handle)) !== false) {
            // إيقاف الفحص مبكراً لو انتهينا من المطلوب
            if ($totalQuestionsLoaded >= $normalLimit && $totalGoldMatched >= $totalGoldNeeded) {
                break;
            }

            $line = trim($line);
            if (empty($line)) continue;
            
            try {
                $data = json_decode($line, true, 512, JSON_THROW_ON_ERROR);
                $meta = $data['metadata'] ?? [];
                $caseNum = trim($meta['case_number'] ?? '');
                
                // 1) محاولة مطابقة سؤال اختبار ذهبي
                if (isset($goldLookup[$caseNum])) {
                    $qaPairs = $data['qa_pairs'] ?? [];
                    foreach ($qaPairs as $qa) {
                        $jsonQ = trim($qa['question'] ?? '');
                        $normJsonQ = $this->normalizeArabic($jsonQ);
                        
                        foreach ($goldLookup[$caseNum] as $normGoldQ => $gIdx) {
                            if ($normGoldQ === $normJsonQ || str_contains($normJsonQ, $normGoldQ) || str_contains($normGoldQ, $normJsonQ)) {
                                if ($goldRecords[$gIdx]['matched_record_data'] === null) {
                                    $goldRecords[$gIdx]['matched_record_data'] = $data;
                                    $goldRecords[$gIdx]['matched_qa'] = $qa;
                                    $totalGoldMatched++;
                                }
                            }
                        }
                    }
                }

                // 2) تحميل الأسئلة العادية إذا لم نصل للحد المطلوب
                if ($totalQuestionsLoaded < $normalLimit) {
                    $recordId = $this->buildRecordId($caseNum);
                    if (!isset($seenRecordIds[$recordId])) {
                        $seenRecordIds[$recordId] = true;
                        $qaPairs = $data['qa_pairs'] ?? [];
                        foreach ($qaPairs as $i => $qa) {
                            if ($totalQuestionsLoaded >= $normalLimit) {
                                break;
                            }

                            $normalQuestions[] = [
                                'record_data' => $data,
                                'qa' => $qa,
                                'qa_index' => $i,
                            ];
                            $totalQuestionsLoaded++;
                        }
                    }
                }
            } catch (\Throwable $e) {
                // تخطي الأخطاء البسيطة
            }
        }
        fclose($handle);
        $this->info("تم تحميل {$totalQuestionsLoaded} سؤال عادي ومطابقة {$totalGoldMatched} / {$totalGoldNeeded} من أسئلة الاختبار بنجاح.");

        // 3. الخلط والدمج (Interleaving)
        // النسبة: لكل 4 أسئلة عادية نضع سؤالاً ذهبياً واحداً
        $this->info("جاري خلط البيانات وإدراجها بالترتيب المناسب للوركبنش...");
        
        $totalNormal = count($normalQuestions);
        $totalGold = count($goldRecords);
        
        $normalIndex = 0;
        $goldIndex = 0;
        $insertedCount = 0;

        $bar = $this->output->createProgressBar($totalNormal + $totalGold);
        $bar->start();

        while ($normalIndex < $totalNormal || $goldIndex < $totalGold) {
            // إدراج 4 أسئلة عادية
            for ($i = 0; $i < 4; $i++) {
                if ($normalIndex < $totalNormal) {
                    $this->insertSingleNormalQuestion($normalQuestions[$normalIndex], $clientId, $refService);
                    $normalIndex++;
                    $insertedCount++;
                    $bar->advance();
                }
            }

            // إدراج 1 سؤال ذهبي للاختبار
            if ($goldIndex < $totalGold) {
                $this->insertGoldRecord($goldRecords[$goldIndex], $clientId);
                $goldIndex++;
                $insertedCount++;
                $bar->advance();
            }
        }

        $bar->finish();
        $this->newLine();
        $this->info("✅ تم الاستيراد والخلط بنجاح! إجمالي المستندات المدرجة: {$insertedCount}");
        
        return self::SUCCESS;
    }

    private function cleanUtf8(?string $string): ?string
    {
        if ($string === null) {
            return null;
        }

        // Convert encoding and drop invalid UTF-8 sequences
        $string = mb_convert_encoding($string, 'UTF-8', 'UTF-8');
        
        // Use iconv to strip remaining invalid byte patterns
        $clean = iconv('UTF-8', 'UTF-8//IGNORE', $string);
        if ($clean !== false) {
            $string = $clean;
        }

        // Drop null bytes
        $string = str_replace(chr(0), '', $string);

        return $string;
    }

    private function insertSingleNormalQuestion(array $item, int $clientId, LegalReferenceService $refService): void
    {
        $data      = $item['record_data'];
        $qa        = $item['qa'];
        $i         = $item['qa_index'];

        $meta      = $data['metadata'] ?? [];
        $caseNum   = trim($meta['case_number'] ?? '');
        $courtType = trim($meta['court_type']  ?? '');
        $date      = trim($meta['date']        ?? '');
        $recordId  = $this->buildRecordId($caseNum);

        $fullText = trim(strip_tags(preg_replace('/<br\s*\/?>/i', "\n", $data['full_case_text'] ?? '')));
        $fullText = $this->cleanUtf8($fullText);

        $record = LegalRecord::where('record_id', $recordId)->first();
        if (!$record) {
            $record = LegalRecord::create([
                'record_id'        => $recordId,
                'domain'           => 'Legal',
                'sub_domain'       => $this->resolveSubDomain($courtType),
                'language'         => 'ar',
                'upload_date'      => preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) ? $date : null,
                'tags'             => $data['tags'] ?? [],
                'source_type'      => 'Court_Judgment',
                'source_reference' => $this->cleanUtf8($caseNum),
                'court_type'       => $this->cleanUtf8($courtType),
                'full_text'        => $fullText,
                'case_summary'     => $this->cleanUtf8($data['case_summary'] ?? null),
            ]);
        }

        $num  = str_pad($i + 1, 3, '0', STR_PAD_LEFT);
        $qaId = "Q-{$num}";

        $question = $this->cleanUtf8($qa['question'] ?? '');
        $answer = $this->cleanUtf8($qa['answer'] ?? '');

        $qaPair = LegalQaPair::create([
            'legal_record_id'  => $record->id,
            'qa_id'            => $qaId,
            'question'         => $question,
            'generated_answer' => $answer,
            'review_status'    => 'Pending',
            'reviewer_id'      => null,
            'corrected_answer' => null,
        ]);

        $firstSystem = null;
        $firstArticleNum = null;
        $firstArticleText = null;

        // إدراج الإحالات المرجعية (Citations)
        foreach ($qa['legal_articles'] ?? [] as $idx => $rawCitation) {
            if (empty($rawCitation)) continue;
            [$systemName, $articleNumber] = $this->parseCitationString($rawCitation);
            
            $legalArticleId = $this->findLegalArticleId($articleNumber, $systemName);

            LegalCitation::create([
                'legal_record_id'  => $record->id,
                'legal_qa_pair_id' => $qaPair->id,
                'system_name'      => $this->cleanUtf8($systemName ?: $rawCitation),
                'article_number'   => $this->cleanUtf8($articleNumber),
                'citation_source'  => 'law',
                'legal_article_id' => $legalArticleId,
            ]);

            if ($idx === 0) {
                $firstSystem = $systemName;
                $firstArticleNum = $articleNumber;
                if ($legalArticleId) {
                    $firstArticleText = \App\Models\LegalArticle::find($legalArticleId)?->content;
                }
            }
        }

        // إنشاء المهمة الأساسية
        $aiTask = AiTask::create([
            'task_type'         => 'legal_verification',
            'original_data'     => $question,
            'ai_suggestion'     => $answer,
            'client_id'         => $clientId,
            'status'            => 'pending',
            'consensus_status'  => 'pending',
            'required_responses'=> 3,
            'task_domain'       => 'law',
            'allow_all_roles'   => true
        ]);

        // ربط المهمة
        LegalTask::create([
            'task_id'            => $aiTask->id,
            'source_type'        => 'legal_qa_pair',
            'source_id'          => $qaPair->id,
            'task_type'          => 'verification',
            'status'             => 'pending',
            'question'           => $question,
            'proposed_answer'    => $answer,
            'law_system_name'    => mb_substr($this->cleanUtf8($firstSystem ?: 'نظام غير محدد') ?? '', 0, 200),
            'law_article_number' => mb_substr($this->cleanUtf8($firstArticleNum ?: 'غير محدد') ?? '', 0, 50),
            'law_article_text'   => $firstArticleText,
            'case_text'          => $fullText,
            'case_reference'     => $this->cleanUtf8($caseNum),
            'domain'             => 'law',
            'source_file'        => 'Radiif_Master_16-5-2026.jsonl',
        ]);
    }

    private function insertGoldRecord(array $gold, int $clientId): void
    {
        $caseNum = $gold['case_reference'] ?: 'Gold-' . uniqid();
        $caseNum = $this->cleanUtf8($caseNum);
        // نضمن فرادة معرفات السجلات الذهبية بإضافة لاحقة خاصة
        $recordId = $this->buildRecordId($caseNum . '-GOLD');

        $goldQuestion = $this->cleanUtf8($gold['question']);
        $goldProposedAnswer = $this->cleanUtf8($gold['proposed_answer']);
        $goldCorrectAnswer = $this->cleanUtf8($gold['correct_answer']);

        // التحقق من وجود بيانات مطابقة من ملف الماستر
        $hasMatch = !empty($gold['matched_record_data']);
        $matchedData = $gold['matched_record_data'] ?? null;
        $matchedQa = $gold['matched_qa'] ?? null;

        if ($hasMatch) {
            $goldCaseText = trim(strip_tags(preg_replace('/<br\s*\/?>/i', "\n", $matchedData['full_case_text'] ?? '')));
            $goldCaseText = $this->cleanUtf8($goldCaseText);
            $courtType = trim($matchedData['metadata']['court_type'] ?? '');
            $subDomain = $this->resolveSubDomain($courtType);
            // دمج وسوم الماستر مع الوسوم الثابتة لأسئلة الاختبار
            $tags = array_unique(array_merge(['gold_standard', 'test_question'], ($matchedData['tags'] ?? [])));
        } else {
            $goldCaseText = $this->cleanUtf8($gold['case_text'] ?: $goldQuestion);
            $subDomain = 'General Law';
            $tags = ['gold_standard', 'test_question'];
        }

        $record = LegalRecord::where('record_id', $recordId)->first();
        if (!$record) {
            $record = LegalRecord::create([
                'record_id'        => $recordId,
                'domain'           => 'Legal',
                'sub_domain'       => $subDomain,
                'language'         => 'ar',
                'tags'             => $tags,
                'source_type'      => 'Test_Question',
                'source_reference' => $caseNum,
                'court_type'       => $hasMatch ? $this->cleanUtf8($matchedData['metadata']['court_type'] ?? '') : 'محكمة تجريبية للتقييم',
                'full_text'        => $goldCaseText,
                'case_summary'     => $hasMatch ? $this->cleanUtf8($matchedData['case_summary'] ?? null) : 'سؤال تقييمي للمحامي للتحقق من جودة ومصداقية الإجابات.',
            ]);
        }

        $qaPair = LegalQaPair::create([
            'legal_record_id'  => $record->id,
            'qa_id'            => 'Q-GOLD',
            'question'         => $goldQuestion,
            'generated_answer' => $goldProposedAnswer, // الإجابة الخاطئة المحددة للاختبار
            'review_status'    => 'Pending',
            'reviewer_id'      => null,
            'corrected_answer' => null,
        ]);

        $firstSystem = null;
        $firstArticleNum = null;
        $firstArticleText = null;

        // إدخال الإحالات المرجعية (Citations)
        if ($hasMatch && !empty($matchedQa['legal_articles'])) {
            foreach ($matchedQa['legal_articles'] as $idx => $rawCitation) {
                if (empty($rawCitation)) continue;
                [$systemName, $articleNumber] = $this->parseCitationString($rawCitation);
                $legalArticleId = $this->findLegalArticleId($articleNumber, $systemName);

                LegalCitation::create([
                    'legal_record_id'  => $record->id,
                    'legal_qa_pair_id' => $qaPair->id,
                    'system_name'      => $this->cleanUtf8($systemName ?: $rawCitation),
                    'article_number'   => $this->cleanUtf8($articleNumber),
                    'citation_source'  => 'law',
                    'legal_article_id' => $legalArticleId,
                ]);

                if ($idx === 0) {
                    $firstSystem = $systemName;
                    $firstArticleNum = $articleNumber;
                    if ($legalArticleId) {
                        $firstArticleText = \App\Models\LegalArticle::find($legalArticleId)?->content;
                    }
                }
            }
        } elseif ($gold['system_name'] || $gold['article_number']) {
            $legalArticleId = $this->findLegalArticleId($gold['article_number'], $gold['system_name']);

            LegalCitation::create([
                'legal_record_id'  => $record->id,
                'legal_qa_pair_id' => $qaPair->id,
                'system_name'      => $this->cleanUtf8($gold['system_name']),
                'article_number'   => $this->cleanUtf8($gold['article_number']),
                'citation_source'  => 'law',
                'legal_article_id' => $legalArticleId,
            ]);

            $firstSystem = $gold['system_name'];
            $firstArticleNum = $gold['article_number'];
            if ($legalArticleId) {
                $firstArticleText = \App\Models\LegalArticle::find($legalArticleId)?->content;
            }
        }

        // إنشاء مهمة الذكاء الاصطناعي (AiTask) كـ Gold Standard
        $aiTask = AiTask::create([
            'task_type' => 'legal_verification',
            'original_data' => $goldQuestion,
            'ai_suggestion' => $goldProposedAnswer,
            'status' => 'pending',
            'payment_status' => 'paid',
            'is_gold_standard' => true,
            'gold_answer' => $goldCorrectAnswer, // الإجابة الصحيحة للمقارنة
            'required_responses' => 1,
            'current_responses' => 0,
            'consensus_status' => 'pending',
            'client_id' => $clientId,
            'task_domain' => 'law',
            'allow_all_roles' => true
        ]);

        // ربط المهمة
        LegalTask::create([
            'task_id' => $aiTask->id,
            'source_type' => 'legal_qa_pair',
            'source_id' => $qaPair->id,
            'task_type' => 'verification',
            'status' => 'pending',
            'question' => $goldQuestion,
            'proposed_answer' => $goldProposedAnswer,
            'correct_answer' => $goldCorrectAnswer,
            'law_system_name' => mb_substr($this->cleanUtf8($firstSystem ?: 'نظام غير محدد') ?? '', 0, 200),
            'law_article_number' => mb_substr($this->cleanUtf8($firstArticleNum ?: 'غير محدد') ?? '', 0, 50),
            'law_article_text' => $firstArticleText,
            'case_reference' => $caseNum,
            'case_text' => $goldCaseText,
            'domain' => 'law',
            'source_file' => 'Radiif_Smart_Golden_100.xlsx',
        ]);
    }

    private function buildRecordId(string $caseNumber): string
    {
        return 'RD-LGL-' . strtoupper(substr(md5($caseNumber), 0, 8));
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

    private function findColumnIndex(array $headers, array $keywords): ?int
    {
        foreach ($headers as $index => $header) {
            foreach ($keywords as $kw) {
                if (str_contains($header, $kw)) {
                    return $index;
                }
            }
        }
        return null;
    }

    private function parseCitationString(string $raw): array
    {
        $raw = $this->convertArabicNumbers($raw);
        $raw = $this->normalizeWrittenNumbers($raw);

        $articleNumber = null;
        if (preg_match('/المادة[^\d]*(\d+)/u', $raw, $m)) {
            $articleNumber = $m[1];
        }

        $systemName = null;
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

        uksort($map, fn($a, $b) => mb_strlen($b) <=> mb_strlen($a));

        foreach ($map as $word => $digit) {
            if (str_contains($text, $word)) {
                $text = str_replace($word, $digit, $text);
            }
        }
        return $text;
    }

    private function findLegalArticleId(?string $articleNumber, ?string $systemName): ?int
    {
        if (! $articleNumber && ! $systemName) return null;

        static $articleCache = [];
        $cacheKey = ($systemName ?? '') . '|||' . ($articleNumber ?? '');
        if (array_key_exists($cacheKey, $articleCache)) {
            return $articleCache[$cacheKey];
        }

        $query = LegalArticle::query();

        if ($systemName) {
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

            $query->where(function($q) use ($searchTerms) {
                foreach ($searchTerms as $term) {
                    $q->orWhere('legislation_title', 'LIKE', "%{$term}%");
                }
            });
        }

        if ($articleNumber) {
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

        if (!$article && $systemName) {
            $cleaned = str_replace(['نظام', 'لائحة', 'قانون'], '', $systemName);
            $article = LegalArticle::where('content', 'LIKE', "%{$cleaned}%")
                ->when($articleNumber, fn($q) => $q->where('content', 'LIKE', "%المادة {$articleNumber}%"))
                ->first();
        }

        $articleCache[$cacheKey] = $article?->id;
        return $articleCache[$cacheKey];
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
        return mb_strtolower(trim($text));
    }
}
