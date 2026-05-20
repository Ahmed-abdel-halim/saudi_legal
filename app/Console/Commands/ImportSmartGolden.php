<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\AiTask;
use App\Models\LegalTask;
use App\Models\User;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\DB;

class ImportSmartGolden extends Command
{
    protected $signature = 'import:smart-golden {file? : Path to the xlsx file}';
    protected $description = 'Import 100 Smart Golden Questions (with wrong answers) to test lawyers';

    public function handle()
    {
        $file = $this->argument('file') ?? base_path('Radiif_Smart_Golden_100.xlsx');

        if (!file_exists($file)) {
            $this->error("الملف غير موجود في المسار: {$file}");
            return self::FAILURE;
        }

        $this->info("جاري تحميل ملف Excel: " . basename($file));

        try {
            $spreadsheet = IOFactory::load($file);
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray();

            if (empty($rows)) {
                $this->error("الملف فارغ!");
                return self::FAILURE;
            }

            // 1. تحديد ترويسات الأعمدة تلقائياً
            $headers = array_map(function($val) {
                return trim(mb_strtolower((string)$val));
            }, $rows[0]);

            $this->info("الأعمدة المكتشفة: " . implode(' | ', $headers));

            // خريطة البحث عن الأعمدة باللغة العربية والانجليزية
            $colMap = [
                'question' => $this->findColumnIndex($headers, ['سؤال', 'question', 'السؤال', 'النص', 'الطلب']),
                'proposed_answer' => $this->findColumnIndex($headers, ['خاطئ', 'غلط', 'مقترح', 'الذكاء', 'incorrect', 'proposed', 'wrong']),
                'correct_answer' => $this->findColumnIndex($headers, ['صحيح', 'معدل', 'الذهبي', 'correct', 'gold', 'right']),
                'system_name' => $this->findColumnIndex($headers, ['نظام', 'اسم النظام', 'قانون', 'law', 'system']),
                'article_number' => $this->findColumnIndex($headers, ['مادة', 'رقم المادة', 'المادة', 'article']),
                'case_reference' => $this->findColumnIndex($headers, ['مرجع', 'رقم القضية', 'reference']),
                'case_text' => $this->findColumnIndex($headers, ['الوقائع', 'نص القضية', 'حكم', 'judgment', 'text'])
            ];

            // طباعة خريطة الأعمدة
            foreach ($colMap as $key => $idx) {
                if ($idx !== null) {
                    $this->info("تم ربط العمود [{$key}] بالعمود رقم " . ($idx + 1) . " ({$rows[0][$idx]})");
                } else {
                    $this->warn("لم يتم تحديد عمود لـ [{$key}]، سيتم استخدام قيم افتراضية.");
                }
            }

            // إزالة سطر الترويسة
            array_shift($rows);

            $clientId = User::where('role', 'client')->first()?->id 
                        ?? User::where('role', 'admin')->first()?->id 
                        ?? User::first()?->id;

            $count = 0;

            DB::transaction(function () use ($rows, $colMap, $clientId, &$count, $file) {
                foreach ($rows as $row) {
                    // التحقق من وجود سؤال
                    $question = $colMap['question'] !== null ? trim((string)($row[$colMap['question']] ?? '')) : '';
                    if (empty($question)) {
                        continue;
                    }

                    $proposedAnswer = $colMap['proposed_answer'] !== null ? trim((string)($row[$colMap['proposed_answer']] ?? '')) : 'إجابة مقترحة بحاجة لمراجعة';
                    $correctAnswer = $colMap['correct_answer'] !== null ? trim((string)($row[$colMap['correct_answer']] ?? '')) : 'الإجابة الصحيحة المعتمدة';
                    $systemName = $colMap['system_name'] !== null ? trim((string)($row[$colMap['system_name']] ?? '')) : 'نظام عام';
                    $articleNumber = $colMap['article_number'] !== null ? trim((string)($row[$colMap['article_number']] ?? '')) : null;
                    $caseRef = $colMap['case_reference'] !== null ? trim((string)($row[$colMap['case_reference']] ?? '')) : 'مرجع ذكي #100';
                    $caseText = $colMap['case_text'] !== null ? trim((string)($row[$colMap['case_text']] ?? '')) : $question;

                    // 1. إنشاء مهمة الذكاء الاصطناعي (AiTask) كـ Gold Standard
                    $aiTask = AiTask::create([
                        'task_type' => 'legal_verification',
                        'original_data' => $question,
                        'ai_suggestion' => $proposedAnswer, // الإجابة الخاطئة المحددة للاختبار
                        'status' => 'pending',
                        'payment_status' => 'paid',
                        'is_gold_standard' => true, // تمييزها كـ Gold Standard
                        'gold_answer' => $correctAnswer, // الإجابة الصحيحة المحددة للمقارنة عند التقييم
                        'required_responses' => 1,
                        'current_responses' => 0,
                        'consensus_status' => 'pending',
                        'client_id' => $clientId,
                        'task_domain' => 'law',
                        'allow_all_roles' => true
                    ]);

                    // 2. إنشاء المهمة القانونية المرتبطة (LegalTask) لتظهر للمحامين في الـ Workbench
                    LegalTask::create([
                        'task_id' => $aiTask->id,
                        'task_type' => 'verification',
                        'status' => 'pending',
                        'question' => $question,
                        'proposed_answer' => $proposedAnswer, // يظهر للمحامي الإجابة الخاطئة ليقوم بتعديلها
                        'correct_answer' => $correctAnswer,
                        'law_system_name' => $systemName,
                        'law_article_number' => $articleNumber,
                        'case_reference' => $caseRef,
                        'case_text' => $caseText,
                        'domain' => 'law',
                        'source_file' => basename($file),
                    ]);

                    $count++;
                }
            });

            $this->info("✅ تم استيراد {$count} أسئلة اختبار ذكية بنجاح كـ Gold Standard لمراقبة جودة المحامين!");
            return self::SUCCESS;

        } catch (\Exception $e) {
            $this->error("حدث خطأ أثناء الاستيراد: " . $e->getMessage());
            return self::FAILURE;
        }
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
}
