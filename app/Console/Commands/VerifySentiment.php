<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Http\UploadedFile;
use App\Services\SentimentCsvService;

class VerifySentiment extends Command
{
    protected $signature = 'verify:sentiment';
    protected $description = 'Verify sentiment analysis CSV processing';

    public function handle()
    {
        $this->info('Starting sentiment verification...');

        $path = storage_path('app/sentiment_test.csv');
        if (!file_exists($path)) {
            $this->error("File not found: $path");
            return 1;
        }

        $file = new UploadedFile($path, 'sentiment_test.csv', 'text/csv', null, true);
        $service = new SentimentCsvService();

        try {
            $result = $service->processCsv($file);
            $this->info('Processing complete.');
            $this->table(['Metric', 'Value'], [
                ['Total Rows', $result['total_rows']],
                ['Tasks Created', $result['tasks_created']],
            ]);
            
            if (!empty($result['domains'])) {
                $this->info('Domains found:');
                foreach ($result['domains'] as $domain => $count) {
                    $this->line(" - $domain: $count");
                }
            }

            if (!empty($result['errors'])) {
                $this->error('Errors encountered:');
                foreach ($result['errors'] as $error) {
                    $this->error(" - $error");
                }
            }

        } catch (\Exception $e) {
            $this->error('Exception: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
