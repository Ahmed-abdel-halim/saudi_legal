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

            $citation = $this->classifyCitation($rawCitation);

            if ($citation['type'] === 'law') {
                $systemName     = $citation['system_name'];
                $articleNumber  = $citation['article_number'];
                $legalArticleId = $this->findLegalArticleId($articleNumber, $systemName);
                $citationSource = 'law';
            } else {
                // نص حر: مبدأ قضائي، استنباط قضائي، إلخ
                $systemName     = null;
                $articleNumber  = null;
                $legalArticleId = null;
                $citationSource = 'other';
            }

            LegalCitation::create([
                'legal_record_id'  => $record->id,
                'legal_qa_pair_id' => $qaPair->id,
                'system_name'      => $this->cleanUtf8($systemName ?: $rawCitation),
                'article_number'   => $this->cleanUtf8($articleNumber),
                'citation_source'  => $citationSource,
                'legal_article_id' => $legalArticleId,
            ]);

            if ($idx === 0) {
                $firstSystem     = $systemName;
                $firstArticleNum = $articleNumber;
                if ($citation['type'] === 'law' && $legalArticleId) {
                    $firstArticleText = \App\Models\LegalArticle::find($legalArticleId)?->content;
                } elseif ($citation['type'] === 'free_text') {
                    // النص الحر يُستخدم مباشرةً كنص المادة القانونية للعرض
                    $firstArticleText = $this->cleanUtf8($rawCitation);
                } elseif ($citation['type'] === 'law' && !$legalArticleId) {
                    // المادة مصنفة كقانون لكن غير موجودة في قاعدة البيانات → نعرض النص الخام كما هو
                    $firstArticleText = $this->cleanUtf8($rawCitation);
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
            'law_system_name'    => mb_substr($this->cleanUtf8($firstSystem ?: ($firstArticleText ? 'مبدأ قضائي' : 'نظام غير محدد')) ?? '', 0, 200),
            'law_article_number' => mb_substr($this->cleanUtf8($firstArticleNum ?: ($firstArticleText ? 'مبدأ' : 'غير محدد')) ?? '', 0, 50),
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

                $citation = $this->classifyCitation($rawCitation);

                if ($citation['type'] === 'law') {
                    $systemName     = $citation['system_name'];
                    $articleNumber  = $citation['article_number'];
                    $legalArticleId = $this->findLegalArticleId($articleNumber, $systemName);
                    $citationSource = 'law';
                } else {
                    $systemName     = null;
                    $articleNumber  = null;
                    $legalArticleId = null;
                    $citationSource = 'other';
                }

                LegalCitation::create([
                    'legal_record_id'  => $record->id,
                    'legal_qa_pair_id' => $qaPair->id,
                    'system_name'      => $this->cleanUtf8($systemName ?: $rawCitation),
                    'article_number'   => $this->cleanUtf8($articleNumber),
                    'citation_source'  => $citationSource,
                    'legal_article_id' => $legalArticleId,
                ]);

                if ($idx === 0) {
                    $firstSystem     = $systemName;
                    $firstArticleNum = $articleNumber;
                    if ($citation['type'] === 'law' && $legalArticleId) {
                        $firstArticleText = \App\Models\LegalArticle::find($legalArticleId)?->content;
                    } elseif ($citation['type'] === 'free_text') {
                        $firstArticleText = $this->cleanUtf8($rawCitation);
                    } elseif ($citation['type'] === 'law' && !$legalArticleId) {
                        // المادة مصنفة كقانون لكن غير موجودة في قاعدة البيانات → نعرض النص الخام كما هو
                        $firstArticleText = $this->cleanUtf8($rawCitation);
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
            } else {
                // المادة غير موجودة في قاعدة البيانات → نعرض النص الخام (اسم النظام + رقم المادة)
                $firstArticleText = $this->cleanUtf8(
                    trim(($gold['system_name'] ?? '') . ' ' . ($gold['article_number'] ?? ''))
                ) ?: null;
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
            'law_system_name' => mb_substr($this->cleanUtf8($firstSystem ?: ($firstArticleText ? 'مبدأ قضائي' : 'نظام غير محدد')) ?? '', 0, 200),
            'law_article_number' => mb_substr($this->cleanUtf8($firstArticleNum ?: ($firstArticleText ? 'مبدأ' : 'غير محدد')) ?? '', 0, 50),
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

    /**
     * تحديد نوع الإحالة: مادة قانونية رسمية أم نص حر (مبدأ قضائي/استنباط/إلخ)؟
     *
     * تُعيد:
     *   ['type' => 'law',       'system_name' => ..., 'article_number' => ..., 'raw_text' => ...]
     *   ['type' => 'free_text', 'system_name' => null, 'article_number' => null, 'raw_text' => ...]
     */
    private function classifyCitation(string $raw): array
    {
        $raw = trim($raw);

        // إذا كان النص طويلاً جداً (أكثر من 120 حرفاً) فهو على الأرجح نص مستنبط وليس مرجعاً قانونياً مختصراً
        if (mb_strlen($raw) > 120) {
            return [
                'type'           => 'free_text',
                'system_name'    => null,
                'article_number' => null,
                'raw_text'       => $raw,
            ];
        }

        // إذا كان المرجع يبدو كنص حر غير نظامي (عقد، اتفاقية، مبدأ قضائي، إلخ)، فهو free_text
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

        // محاولة تحليل وتفكيك الإحالة
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
        
        // تنظيف الأقواس المحيطة بالأرقام أو الكلمات التي تلي "المادة" أو "مادة" مباشرةً لتسهيل المطابقة والتحليل
        // مثال: "المادة (51)" -> "المادة 51"، "المادة (السادسة والثلاثون)" -> "المادة السادسة والثلاثون"
        $rawClean = preg_replace('/(?:المادة|مادة)\s*(?:رقم\s*)?\(\s*([^)]+)\s*\)/ui', 'المادة $1', $rawClean);

        $articleNumber = null;
        $systemName = null;

        // 1. محاولة استخراج الأرقام مباشرة بعد كلمة المادة (مثال: "المادة 29")
        if (preg_match('/المادة\s+(?:رقم\s+)?(\d+)/ui', $rawClean, $m)) {
            $articleNumber = $m[1];
        } else {
            // 2. إذا لم تكن هناك أرقام، نبحث عن الكلمات الترتيبية العربية (حروف) بعد كلمة "المادة" وحتى كلمة الفصل (من/في/بموجب/وفق) أو نهاية النص
            if (preg_match('/المادة\s+(.*?)(?:\s+(?:من|في|بموجب|وفق)|$)/ui', $rawClean, $m)) {
                $written = trim($m[1]);
                $articleNumber = $this->parseWrittenArabicNumber($written);
            }
        }

        // استخراج اسم النظام المرجعي:
        // الحالة أ: "المادة X من/في [اسم النظام]"
        if (preg_match('/المادة\s+.+?\s+(?:من|في|وفق|بموجب)\s+(.+)$/ui', $rawClean, $m)) {
            $systemName = trim($m[1]);
        }
        // الحالة ب: "الفقرة X من المادة Y من/في [اسم النظام]"
        elseif (preg_match('/من\s+المادة\s+.+?\s+(?:من|في|وفق|بموجب)\s+(.+)$/ui', $rawClean, $m)) {
            $systemName = trim($m[1]);
        }
        // الحالة ج: "[اسم النظام] المادة X" أو "[اسم النظام] المادة [الكلمات]" (بدون حروف جر)
        elseif (preg_match('/^(.+?)\s+المادة\s+/ui', $rawClean, $m)) {
            $systemName = trim($m[1]);
        }
        // الحالة د: لا تحتوي الإحالة على كلمة "المادة" على الإطلاق
        elseif (!preg_match('/المادة/ui', $rawClean)) {
            $systemName = $rawClean;
        }

        // تنظيف اسم النظام من السوابق والرموز غير المرغوبة
        if ($systemName) {
            $systemName = preg_replace('/^(?:نظام|لائحة|قانون)\s+/ui', '', $systemName);
            $systemName = trim($systemName, " \t\n\r\0\x0B().");
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
            return null; // A citation MUST have an article number to match a specific LegalArticle!
        }

        $systemName = trim($systemName);
        if (empty($systemName)) {
            return null;
        }

        // Avoid matching non-law references like contracts, judgments, evidence, etc.
        $isNonLaw = preg_match('/(?:العقد|اتفاق|محضر|إفادة|تقرير|بينة|سند|فاتورة|كشف|خطاب|مبدأ|قاعدة شرعية|فقه|أسباب|منطوق|تاريخ|مستنبط|استنباط|قضائي مستقر)/ui', $systemName);
        if ($isNonLaw) {
            return null;
        }

        static $articleCache = [];
        $cacheKey = $systemName . '|||' . $articleNumber;
        if (array_key_exists($cacheKey, $articleCache)) {
            return $articleCache[$cacheKey];
        }

        $query = LegalArticle::query();

        // 1. Match the legislation/system name
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
                $q->orWhere('legislation_title', $term)
                  ->orWhere('legislation_title', 'LIKE', "%{$term}%");
            }
        });

        // 2. Match the article number exactly or as ordinal
        $textNum = is_numeric($articleNumber) ? $this->arabicOrdinal((int) $articleNumber) : null;

        // We will match exact patterns in article_title or content:
        // article_title can be: "المادة الأولى", "المادة 1", "المادة (1)", "المادة الحادية والعشرون", etc.
        // We must prevent substring matching where "العشرون" matches "الحادية والعشرون" (which is Article 21, not 20).
        $query->where(function($q) use ($articleNumber, $textNum) {
            $exactTitles = [];
            
            // Format 1: "المادة X" or "المادة (X)" where X is numeric
            $exactTitles[] = "المادة {$articleNumber}";
            $exactTitles[] = "المادة ({$articleNumber})";
            $exactTitles[] = "المادة {$articleNumber} مكرر";
            $exactTitles[] = "المادة ({$articleNumber}) مكرر";
            
            // Format 2: "المادة X" where X is written Arabic (e.g. "الأولى", "العشرون")
            if ($textNum) {
                $exactTitles[] = "المادة {$textNum}";
                $exactTitles[] = "المادة ({$textNum})";
                $exactTitles[] = "المادة {$textNum} مكرر";
            }

            $q->whereIn('article_title', $exactTitles);
            
            foreach ($exactTitles as $title) {
                $q->orWhere('article_title', 'LIKE', $title);
            }

            // Fallback: If not matched by exact title, search inside content but ONLY with strict word boundary or exact "المادة X" phrase
            $q->orWhere('content', 'LIKE', "%المادة {$articleNumber}%")
              ->orWhere('content', 'LIKE', "%المادة ({$articleNumber})%");
              
            if ($textNum) {
                $q->orWhere('content', 'LIKE', "%المادة {$textNum}%")
                  ->orWhere('content', 'LIKE', "%المادة ({$textNum})%");
            }
        });

        $articles = $query->get();
        $matchedArticle = null;
        
        // Find best exact match
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

        // Substring boundary filter fallback
        if (!$matchedArticle && $articles->isNotEmpty()) {
            foreach ($articles as $art) {
                $title = trim($art->article_title);
                
                // Skip matching "الحادية والعشرون" when we want "العشرون"
                if ($textNum && $articleNumber % 10 === 0) {
                    if (str_contains($title, 'و' . $textNum)) {
                        continue; // Skip "الحادية والعشرون", "الثانية والعشرون", etc.
                    }
                }

                $matchedArticle = $art;
                break;
            }
        }

        $articleCache[$cacheKey] = $matchedArticle?->id;
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
