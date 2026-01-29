<?php

namespace App\Services;

use App\Models\GovernanceLog;
use App\Models\TaskConsensus;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class CsvAnalysisService
{
    private const REQUIRED_HEADERS = [
        'task_id', 'expert_id', 'answer', 'is_gold_standard', 'gold_answer', 'submitted_at'
    ];

    private const TRUST_SCORE_PENALTY = 10;
    
    /**
     * Analyze uploaded CSV file
     */
    public function analyze(UploadedFile $file): array
    {
        // 1. Parse and Validate CSV
        $records = $this->parseCsv($file);
        
        // 2. Initialize Virtual State
        $expertScores = $this->loadInitialExpertScores(array_column($records, 'expert_id'));
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
            // 3. Process each task group
            foreach ($tasks as $taskId => $taskRecords) {
                
                // A. Gold Standard Validation
                foreach ($taskRecords as $record) {
                    if ($this->isGoldStandard($record)) {
                        $expertId = $record['expert_id'];
                        $isCorrect = $this->validateGoldAnswer($record);
                        
                        // Update virtual score
                        if (!$isCorrect) {
                            $results['gold_failures']++;
                            $oldScore = $expertScores[$expertId];
                            $newScore = max(0, $oldScore - self::TRUST_SCORE_PENALTY);
                            $expertScores[$expertId] = $newScore;
                            
                            // Log failure
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

                            // Flag expert if score drops low
                            if ($newScore < 60) {
                                $results['flagged_experts'][$expertId] = $newScore;
                            }
                        }
                    }
                }

                // B. Calculate Consensus
                $consensus = $this->calculateConsensus($taskRecords);
                
                // Track stats
                if ($consensus['type'] === 'perfect_match') $results['perfect_consensus']++;
                elseif ($consensus['type'] === 'majority_vote') $results['majority_vote']++;
                elseif ($consensus['type'] === 'conflict') $results['conflicts']++;

                // Save consensus record
                TaskConsensus::create([
                    'task_id' => $taskId, // Assuming virtual or existing ID
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
            
            // Calculate final metrics
            if ($results['total_tasks'] > 0) {
                $accuracy = ($results['perfect_consensus'] + $results['majority_vote']) / $results['total_tasks'] * 100;
                $results['accuracy_rate'] = round($accuracy, 2);
            } else {
                $results['accuracy_rate'] = 0;
            }

            return $results;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    private function parseCsv(UploadedFile $file): array
    {
        $handle = fopen($file->getRealPath(), 'r');
        $headers = fgetcsv($handle);

        // Validate Headers
        if (!$headers || count(array_diff(self::REQUIRED_HEADERS, $headers)) > 0) {
            fclose($handle);
            throw new \Exception('Invalid CSV headers. Required: ' . implode(', ', self::REQUIRED_HEADERS));
        }

        $records = [];
        while (($row = fgetcsv($handle)) !== false) {
            $data = array_combine($headers, $row);
            
            // Basic data cleanup
            $data['is_gold_standard'] = filter_var($data['is_gold_standard'], FILTER_VALIDATE_BOOLEAN);
            // Decode JSON answers if they are strings
            $data['answer'] = $this->decodeJson($data['answer']);
            $data['gold_answer'] = $this->decodeJson($data['gold_answer']);
            
            $records[] = $data;
        }
        fclose($handle);

        if (empty($records)) {
            throw new \Exception('CSV file is empty');
        }

        return $records;
    }

    private function loadInitialExpertScores(array $expertIds): array
    {
        $experts = User::whereIn('id', array_unique($expertIds))->pluck('trust_score', 'id')->toArray();
        // Default to 100 if expert not found in DB
        foreach ($expertIds as $id) {
            if (!isset($experts[$id])) {
                $experts[$id] = 100.00;
            }
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

    private function decodeJson($data)
    {
        if (is_string($data)) {
            $decoded = json_decode($data, true);
            return (json_last_error() === JSON_ERROR_NONE) ? $decoded : $data;
        }
        return $data;
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
             return [
                'type' => 'perfect_match',
                'final_answer' => $mostCommonAnswer,
                'confidence' => 100.00
            ];
        } elseif ($maxCount >= 2) {
            return [
                'type' => 'majority_vote',
                'final_answer' => $mostCommonAnswer,
                'confidence' => 66.67
            ];
        } else {
            return [
                'type' => 'conflict',
                'final_answer' => null,
                'confidence' => 0.00,
                'notes' => 'Total disagreement in CSV batch'
            ];
        }
    }
}
