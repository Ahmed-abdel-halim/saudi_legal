<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Sharia\FatwaHarvesterService;
use App\Models\ShariaRecord;

class FatwaHarvestCommand extends Command
{
    protected $signature = 'sharia:harvest {--file= : Path to JSON or JSONL file to ingest} {--url= : URL endpoint to fetch fatwas from}';
    protected $description = 'Ingest and deduplicate Islamic fatwas into the sovereign knowledge base';

    public function handle(FatwaHarvesterService $harvester)
    {
        $this->info("⚡ Starting Sharia Fatwa Harvester Pipeline...");

        $filePath = $this->option('file');

        if ($filePath && file_exists($filePath)) {
            $this->info("📂 Reading records from file: {$filePath}");
            $content = file_get_contents($filePath);
            $lines = explode("\n", trim($content));
            
            $created = 0;
            $skipped = 0;
            $duplicates = 0;

            $bar = $this->output->createProgressBar(count($lines));
            $bar->start();

            foreach ($lines as $line) {
                if (empty(trim($line))) continue;
                $row = json_decode($line, true);
                if (!$row) continue;

                $res = $harvester->ingestRecord($row);
                if ($res['status'] === 'created') $created++;
                elseif ($res['status'] === 'duplicate') $duplicates++;
                else $skipped++;

                $bar->advance();
            }

            $bar->finish();
            $this->newLine();

            $this->table(['Status', 'Count'], [
                ['Created (New Fatwas)', $created],
                ['Duplicates (Filtered Out)', $duplicates],
                ['Skipped / Invalid', $skipped],
                ['Total Records in DB', ShariaRecord::count()],
            ]);

            return 0;
        }

        $this->info("ℹ️ No file specified. Use --file=path/to/fatwas.jsonl or run php artisan sharia:seed-core");
        return 0;
    }
}
