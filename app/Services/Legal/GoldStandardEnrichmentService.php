<?php

namespace App\Services\Legal;

use App\Models\LegalQaPair;
use App\Models\LegalTask;
use App\Models\AiTask;

/**
 * يجيب بيانات سؤال الاختبار (Gold Standard) من ملف الـ JSONL الكبير:
 * - نص القضية الكامل
 * - المواد القانونية المرتبطة بالسؤال
 * - الإجابة الغلط المقصودة
 */
class GoldStandardEnrichmentService
{
    private string $jsonlPath;

    public function __construct()
    {
        $this->jsonlPath = base_path('Radiif_Master_16-5-2026.jsonl');
    }

    /**
     * يجيب بيانات الإثراء للـ QA pair لو كان gold standard
     *
     * @return array|null [
     *   'case_text'      => string,
     *   'legal_articles' => array of strings,
     *   'wrong_answer'   => string|null,
     *   'case_number'    => string,
     * ]
     */
    public function enrich(LegalQaPair $qa): ?array
    {
        // 1. جيب رقم القضية من الـ LegalRecord
        $caseNumber = $qa->record?->source_reference;
        if (!$caseNumber) return null;

        // 2. جيب الـ AiTask المرتبط عشان نعرف لو gold standard وناخد الإجابة الغلط
        $legalTask = LegalTask::where('source_id', $qa->id)
            ->where('source_type', 'legal_qa_pair')
            ->first();

        $wrongAnswer = null;
        if ($legalTask) {
            $aiTask = AiTask::find($legalTask->task_id);
            if ($aiTask && $aiTask->is_gold_standard) {
                $goldAnswer = $aiTask->gold_answer;
                // gold_answer ممكن يكون array أو string
                if (is_array($goldAnswer)) {
                    $wrongAnswer = $goldAnswer['wrong_answer']
                        ?? $goldAnswer['incorrect_answer']
                        ?? $goldAnswer['answer']
                        ?? null;
                } else {
                    $wrongAnswer = $goldAnswer;
                }
            } else {
                // مش gold standard — مفيش داعي للإثراء
                return null;
            }
        } else {
            return null;
        }

        // 3. ابحث في الـ JSONL
        $record = $this->findInJsonl($caseNumber, $qa->question);
        if (!$record) return null;

        return [
            'case_number'    => $caseNumber,
            'case_text'      => $record['full_case_text'] ?? '',
            'legal_articles' => $record['legal_articles'] ?? [],
            'wrong_answer'   => $wrongAnswer,
        ];
    }

    /**
     * يبحث في الـ JSONL على القضية ويجيب المواد المرتبطة بالسؤال
     */
    private function findInJsonl(string $caseNumber, string $question): ?array
    {
        if (!file_exists($this->jsonlPath)) return null;

        $handle = fopen($this->jsonlPath, 'r');
        if (!$handle) return null;

        // نظّف رقم القضية من أي مسافات أو صفر بادئ
        $caseNumber = trim($caseNumber);

        while (($line = fgets($handle)) !== false) {
            $line = trim($line);
            if (empty($line)) continue;

            $data = json_decode($line, true);
            if (!$data) continue;

            $recordCaseNumber = trim($data['metadata']['case_number'] ?? '');

            if ($recordCaseNumber !== $caseNumber) continue;

            // وجدنا القضية — دور على الـ qa_pair المطابق للسؤال
            fclose($handle);

            $legalArticles = [];
            $fullCaseText  = $data['full_case_text'] ?? '';

            if (!empty($data['qa_pairs'])) {
                foreach ($data['qa_pairs'] as $pair) {
                    // مطابقة تقريبية للسؤال (أول 30 حرف)
                    $pairQ    = trim($pair['question'] ?? '');
                    $inputQ   = trim($question);
                    $similarity = similar_text(
                        mb_substr($pairQ, 0, 50),
                        mb_substr($inputQ, 0, 50)
                    );

                    if ($similarity > 20 || $pairQ === $inputQ) {
                        $legalArticles = $pair['legal_articles'] ?? [];
                        break;
                    }
                }

                // لو ما لقيناش مطابقة، خد المواد من أول qa_pair
                if (empty($legalArticles) && !empty($data['qa_pairs'][0]['legal_articles'])) {
                    $legalArticles = $data['qa_pairs'][0]['legal_articles'];
                }
            }

            return [
                'full_case_text' => $fullCaseText,
                'legal_articles' => $legalArticles,
            ];
        }

        fclose($handle);
        return null;
    }
}
