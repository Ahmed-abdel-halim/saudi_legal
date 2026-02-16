<?php

namespace App\Services;

use App\Models\LinguisticTask;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class SubmissionService
{
    protected ContentClassificationService $classifier;

    public function __construct(ContentClassificationService $classifier)
    {
        $this->classifier = $classifier;
    }

    /**
     * Create a new submission from user input
     *
     * @param User $user
     * @param string $text
     * @param string|null $proposedClassification
     * @return LinguisticTask
     * @throws \Exception
     */
    public function create(User $user, string $text, ?string $proposedClassification = null): LinguisticTask
    {
        // Generate content hash for duplicate detection
        $contentHash = md5(trim($text));

        // Anti-spam: Check for duplicate content
        $existingTask = LinguisticTask::where('content_hash', $contentHash)->first();
        if ($existingTask) {
            throw new \Exception('This content has already been submitted.');
        }

        // Anti-spam: Check for recent submissions from same user (within 5 minutes)
        $recentSubmission = LinguisticTask::where('created_at', '>', now()->subMinutes(5))
            ->whereHas('assignments', function($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->exists();

        if ($recentSubmission) {
            throw new \Exception('Please wait before submitting another task.');
        }

        // Classify the content
        $category = $this->classifier->classify($text);

        // Create the task
        $task = LinguisticTask::create([
            'task_type' => 'sentiment',
            'comment_text' => $text,
            'proposed_classification' => $proposedClassification,
            'content_hash' => $contentHash,
            'category' => $category,
            'status' => 'pending',
            'assigned_count' => 0,
            'priority' => 0,
        ]);

        return $task;
    }

    /**
     * Create tasks from CSV upload (Admin feature)
     *
     * @param string $csvPath
     * @param string $filename
     * @return array
     */
    public function createFromCsv(string $csvPath, string $filename): array
    {
        $tasks = [];
        $errors = [];
        $rowNumber = 0;

        if (($handle = fopen($csvPath, 'r')) !== false) {
            // Skip header row
            fgetcsv($handle);

            while (($data = fgetcsv($handle)) !== false) {
                $rowNumber++;

                try {
                    // Expecting: [comment_text, proposed_classification]
                    $text = $data[0] ?? null;
                    $proposedClassification = $data[1] ?? null;

                    if (empty($text)) {
                        $errors[] = "Row {$rowNumber}: Empty text";
                        continue;
                    }

                    // Generate content hash
                    $contentHash = md5(trim($text));

                    // Check for duplicates
                    if (LinguisticTask::where('content_hash', $contentHash)->exists()) {
                        $errors[] = "Row {$rowNumber}: Duplicate content";
                        continue;
                    }

                    // Classify content
                    $category = $this->classifier->classify($text);

                    // Create task
                    $task = LinguisticTask::create([
                        'task_type' => 'sentiment',
                        'comment_text' => $text,
                        'proposed_classification' => $proposedClassification,
                        'content_hash' => $contentHash,
                        'category' => $category,
                        'status' => 'pending',
                        'assigned_count' => 0,
                        'priority' => 0,
                        'csv_file' => $filename,
                        'row_number' => $rowNumber,
                    ]);

                    $tasks[] = $task;

                } catch (\Exception $e) {
                    $errors[] = "Row {$rowNumber}: {$e->getMessage()}";
                }
            }

            fclose($handle);
        }

        return [
            'tasks' => $tasks,
            'errors' => $errors,
            'total' => count($tasks),
        ];
    }
}
