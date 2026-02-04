<?php

namespace App\Services;

use App\Models\AiTask;
use App\Models\TaskConsensus;
use App\Models\GovernanceLog;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Exception;

class CsvAnalysisService
{
    private const REQUIRED_HEADERS = [
        'task_id',
        'expert_id',
        'answer',
        'is_gold_standard',
        'gold_answer',
        'submitted_at',
        'task_type'
    ];

    private const TRUST_SCORE_PENALTY = 10;

    public function analyze(UploadedFile $file): array
    {
        $records = $this->parseCsv($file);

        $csvExpertIds = array_unique(array_column($records, 'expert_id'));
        $existingIds = User::whereIn('id', $csvExpertIds)->pluck('id')->toArray();
        $missingIds = array_diff($csvExpertIds, $existingIds);

        if (!empty($missingIds)) {
            throw new Exception('Integrity Error: Expert IDs do not exist: ' . implode(', ', $missingIds));
        }

        $expertScores = $this->loadInitialExpertScores($csvExpertIds);
        $tasks = $this->groupRecordsByTask($records);

        $results = [
            'total_rows' => count($records),
            'total_tasks' => count($tasks),
            'gold_failures' => 0,
            'conflicts' => 0,
            'perfect_consensus' => 0,
            'majority_vote' => 0,
            'flagged_experts' => [],
            'logs_created' => 0
        ];

        DB::beginTransaction();
        try {
            foreach ($tasks as $taskId => $taskRecords) {
                $taskType = $taskRecords[0]['task_type'] ?? 'text_correction';
                $existingTask = AiTask::find($taskId);
                $csvTaskContent = $taskRecords[0]['original_data'] ?? null;

                if ($existingTask) {
                    $taskContent = $csvTaskContent ?? $existingTask->original_data;
                } else {
                    $taskContent = $csvTaskContent ?? $this->extractFirstTextColumn($taskRecords[0]);

                    if (!$this->isProbablyTextContent($taskContent)) {
                        throw new Exception("CSV is missing a content/original_data column for task_id {$taskId}");
                    }

                    // Ensure UTF-8 encoding
                    $taskContent = mb_convert_encoding(
                        $taskContent,
                        'UTF-8',
                        ['UTF-8', 'ISO-8859-6', 'Windows-1256']
                    );
                }

                AiTask::updateOrCreate(
                    ['id' => $taskId],
                    [
                        'task_type' => $taskType,
                        'original_data' => $taskContent,
                        'required_responses' => max(3, count($taskRecords)),
                        'current_responses' => 0,
                        'status' => 'Pending'
                    ]
                );

                // Process Gold Standard
                foreach ($taskRecords as $record) {
                    if ($this->isGoldStandard($record)) {
                        $expertId = $record['expert_id'];
                        $isCorrect = $this->validateGoldAnswer($record);

                        if (!$isCorrect) {
                            $results['gold_failures']++;
                            $oldScore = $expertScores[$expertId];
                            $newScore = max(0, $oldScore - self::TRUST_SCORE_PENALTY);
                            $expertScores[$expertId] = $newScore;

                            GovernanceLog::create([
                                'expert_id' => $expertId,
                                'task_id' => $taskId,
                                'event_type' => 'gold_task_failed',
                                'event_data' => [
                                    'source' => 'csv_analysis',
                                    'expected' => $record['gold_answer'],
                                    'submitted' => $record['answer']
                                ],
                                'trust_score_before' => $oldScore,
                                'trust_score_after' => $newScore
                            ]);
                            $results['logs_created']++;

                            if ($newScore < 60) {
                                $results['flagged_experts'][$expertId] = $newScore;
                            }
                        }
                    }
                }

                // Calculate consensus
                $consensus = $this->calculateConsensus($taskRecords);

                if ($consensus['type'] === 'perfect_match') $results['perfect_consensus']++;
                elseif ($consensus['type'] === 'majority_vote') $results['majority_vote']++;
                elseif ($consensus['type'] === 'conflict') $results['conflicts']++;

                TaskConsensus::create([
                    'task_id' => $taskId,
                    'expert_answers' => $taskRecords,
                    'final_answer' => $consensus['final_answer'],
                    'confidence_level' => $consensus['confidence'],
                    'consensus_type' => $consensus['type'],
                    'conflict_notes' => $consensus['notes'] ?? null,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }

            DB::commit();

            if ($results['total_tasks'] > 0) {
                $accuracy = ($results['perfect_consensus'] + $results['majority_vote']) / $results['total_tasks'] * 100;
                $results['accuracy_rate'] = round($accuracy, 2);
            } else {
                $results['accuracy_rate'] = 0;
            }

            return $results;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    private function parseCsv(UploadedFile $file): array
    {
        $handle = fopen($file->getRealPath(), 'r');
        $headers = fgetcsv($handle);

        if (!$headers) {
            fclose($handle);
            throw new Exception('CSV file is empty or could not be read');
        }

        if (isset($headers[0])) {
            $bom = pack('H*', 'EFBBBF');
            $headers[0] = preg_replace("/^$bom/", '', $headers[0]);
        }

        $normalizedHeaders = array_map(fn($h) => strtolower(trim($h)), $headers);
        $expectedHeaders = array_map('strtolower', self::REQUIRED_HEADERS);

        $missing = array_diff($expectedHeaders, $normalizedHeaders);
        if (count($missing) > 0) {
            fclose($handle);
            throw new Exception('Invalid CSV headers. Missing: ' . implode(', ', $missing));
        }

        $records = [];
        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) !== count($normalizedHeaders)) continue;

            $data = array_combine($normalizedHeaders, $row);
            $data['is_gold_standard'] = filter_var($data['is_gold_standard'], FILTER_VALIDATE_BOOLEAN);
            $data['answer'] = $this->decodeJson($data['answer']);
            $data['gold_answer'] = $this->decodeJson($data['gold_answer']);
            $data['confidence'] = $data['confidence'] ?? 0;

            $records[] = $data;
        }

        fclose($handle);

        if (empty($records)) throw new Exception('CSV file is empty');

        return $records;
    }

    private function decodeJson($data)
    {
        if (is_string($data)) {
            $decoded = json_decode($data, true);
            return json_last_error() === JSON_ERROR_NONE ? $decoded : $data;
        }
        return $data;
    }

    private function loadInitialExpertScores(array $expertIds): array
    {
        $experts = User::whereIn('id', array_unique($expertIds))->pluck('trust_score', 'id')->toArray();
        foreach ($expertIds as $id) {
            if (!isset($experts[$id])) $experts[$id] = 100.00;
        }
        return $experts;
    }

    private function groupRecordsByTask(array $records): array
    {
        $tasks = [];
        foreach ($records as $record) {
            $tasks[$record['task_id']][] = $record;
        }
        return $tasks;
    }

    private function isGoldStandard(array $record): bool
    {
        return !empty($record['is_gold_standard']) && !empty($record['gold_answer']);
    }

    private function validateGoldAnswer(array $record): bool
    {
        return json_encode($record['answer']) === json_encode($record['gold_answer']);
    }

    private function extractFirstTextColumn(array $record)
    {
        foreach ($record as $key => $value) {
            if (!in_array($key, ['task_id', 'expert_id', 'answer', 'is_gold_standard', 'gold_answer', 'submitted_at', 'task_type', 'confidence', 'confidence_level']) && !empty($value)) {
                return $value;
            }
        }
        return null;
    }

    private function isProbablyTextContent($value): bool
    {
        if ($value === null) return false;

        $value = trim((string)$value);
        if ($value === '') return false;

        return preg_match('/\\p{L}/u', $value) === 1;
    }

    private function calculateConsensus(array $taskRecords): array
    {
        $answers = array_column($taskRecords, 'answer');
        $answerCounts = [];
        foreach ($answers as $answer) {
            $key = json_encode($answer);
            $answerCounts[$key] = ($answerCounts[$key] ?? 0) + 1;
        }

        arsort($answerCounts);
        $maxCount = reset($answerCounts);
        $mostCommonAnswer = json_decode(key($answerCounts), true);

        if ($maxCount === count($taskRecords) && count($taskRecords) >= 2) {
            return ['type' => 'perfect_match', 'final_answer' => $mostCommonAnswer, 'confidence' => 100.00];
        } elseif ($maxCount >= 2) {
            return ['type' => 'majority_vote', 'final_answer' => $mostCommonAnswer, 'confidence' => 66.67];
        } else {
            return ['type' => 'conflict', 'final_answer' => null, 'confidence' => 0.00, 'notes' => 'Total disagreement in CSV batch'];
        }
    }
}
