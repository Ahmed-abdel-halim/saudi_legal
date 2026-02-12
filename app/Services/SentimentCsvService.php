<?php

namespace App\Services;

use App\Models\LinguisticTask;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Exception;

class SentimentCsvService
{
    private const REQUIRED_HEADERS = [
        'tweet text no handles',
        'التصنيف',
        'المجال'
    ];

    private const VALID_CLASSIFICATIONS = ['إيجابي', 'سلبي', 'محايد'];
    
    private const VALID_DOMAINS = [
        'طب',
        'هندسة',
        'محاماة',
        'تعليم',
        'تقنية',
        'أعمال',
        'عام' // General/Other
    ];

    /**
     * Process sentiment analysis CSV file
     */
    public function processCsv(UploadedFile $file): array
    {
        $records = $this->parseCsv($file);
        
        $results = [
            'total_rows' => count($records),
            'tasks_created' => 0,
            'errors' => [],
            'domains' => [],
        ];

        DB::beginTransaction();
        try {
            foreach ($records as $index => $record) {
                try {
                    $task = LinguisticTask::create([
                        'task_type' => 'sentiment',
                        'comment_text' => $record['comment_text'],
                        'proposed_classification' => $record['proposed_classification'],
                        'domain' => $record['domain'],
                        'status' => 'pending',
                        'csv_file' => $file->getClientOriginalName(),
                        'row_number' => $index + 2, // +2 for header row and 0-index
                    ]);

                    $results['tasks_created']++;
                    
                    // Track domains
                    if (!isset($results['domains'][$record['domain']])) {
                        $results['domains'][$record['domain']] = 0;
                    }
                    $results['domains'][$record['domain']]++;
                    
                } catch (Exception $e) {
                    $results['errors'][] = "Row " . ($index + 2) . ": " . $e->getMessage();
                }
            }

            DB::commit();
            return $results;
            
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Parse CSV file and validate data
     */
    private function parseCsv(UploadedFile $file): array
    {
        $handle = fopen($file->getRealPath(), 'r');
        $headers = fgetcsv($handle);

        if (!$headers) {
            fclose($handle);
            throw new Exception('CSV file is empty or could not be read');
        }

        // Remove BOM if present
        if (isset($headers[0])) {
            $bom = pack('H*', 'EFBBBF');
            $headers[0] = preg_replace("/^$bom/", '', $headers[0]);
        }

        // Normalize headers (lowercase, trim)
        $normalizedHeaders = array_map(fn($h) => strtolower(trim($h)), $headers);
        
        // Validate required headers
        $this->validateHeaders($normalizedHeaders);

        $records = [];
        $rowNumber = 1; // Start from 1 (after header)
        
        while (($row = fgetcsv($handle)) !== false) {
            $rowNumber++;
            
            if (count($row) !== count($normalizedHeaders)) {
                continue; // Skip malformed rows
            }

            $data = array_combine($normalizedHeaders, $row);
            
            // Extract and validate data
            try {
                $record = $this->extractRecord($data, $rowNumber);
                $records[] = $record;
            } catch (Exception $e) {
                // Log error but continue processing
                continue;
            }
        }

        fclose($handle);

        if (empty($records)) {
            throw new Exception('No valid records found in CSV file');
        }

        return $records;
    }

    /**
     * Validate CSV headers
     */
    private function validateHeaders(array $headers): void
    {
        $missing = [];
        
        foreach (self::REQUIRED_HEADERS as $required) {
            if (!in_array($required, $headers)) {
                $missing[] = $required;
            }
        }

        if (!empty($missing)) {
            throw new Exception('Invalid CSV headers. Missing: ' . implode(', ', $missing));
        }
    }

    /**
     * Extract and validate record data
     */
    private function extractRecord(array $data, int $rowNumber): array
    {
        $commentText = trim($data['tweet text no handles'] ?? '');
        $classification = trim($data['التصنيف'] ?? '');
        $domain = trim($data['المجال'] ?? '');

        // Validate comment text
        if (empty($commentText)) {
            throw new Exception("Row {$rowNumber}: Comment text is empty");
        }

        // Validate classification
        if (!in_array($classification, self::VALID_CLASSIFICATIONS)) {
            throw new Exception("Row {$rowNumber}: Invalid classification '{$classification}'. Must be one of: " . implode(', ', self::VALID_CLASSIFICATIONS));
        }

        // Validate domain
        if (!in_array($domain, self::VALID_DOMAINS)) {
            throw new Exception("Row {$rowNumber}: Invalid domain '{$domain}'. Must be one of: " . implode(', ', self::VALID_DOMAINS));
        }

        return [
            'comment_text' => $commentText,
            'proposed_classification' => $classification,
            'domain' => $domain,
        ];
    }

    /**
     * Get statistics about available tasks by domain
     */
    public function getTaskStatsByDomain(): array
    {
        return LinguisticTask::where('task_type', 'sentiment')
            ->where('status', 'pending')
            ->select('domain', DB::raw('count(*) as count'))
            ->groupBy('domain')
            ->pluck('count', 'domain')
            ->toArray();
    }
}
