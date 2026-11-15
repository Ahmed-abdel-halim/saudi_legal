<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Sharia\FatwaHarvesterService;
use App\Models\ShariaRecord;
use Illuminate\Support\Facades\DB;

class FatwaHarvestCommand extends Command
{
    protected $signature = 'sharia:harvest {--file= : Path to JSON or JSONL file to ingest} {--dir= : Directory containing JSONL files}';
    protected $description = 'Ingest and deduplicate Islamic fatwas into the sovereign knowledge base';

    public function handle(FatwaHarvesterService $harvester)
    {
        $this->info("⚡ Starting Sharia Fatwa Harvester Pipeline...");

        $files = [];
        $dir = $this->option('dir');
        $file = $this->option('file');

        if ($dir && is_dir($dir)) {
            $files = glob(rtrim($dir, '/\\') . '/*.jsonl');
            sort($files);
        } elseif ($file && file_exists($file)) {
            $files = [$file];
        } else {
            // Default check E:\Radiif\فتوي
            $defaultDir = base_path('فتوي');
            if (is_dir($defaultDir)) {
                $files = glob(rtrim($defaultDir, '/\\') . '/*.jsonl');
                sort($files);
            }
        }

        if (empty($files)) {
            $this->error("❌ No valid JSONL files found. Please specify --file=... or --dir=...");
            return 1;
        }

        $this->info("📂 Found " . count($files) . " file(s) to process:");
        foreach ($files as $f) {
            $this->line("   - " . basename($f));
        }

        $totalCreated = 0;
        $totalDuplicates = 0;
        $totalSkipped = 0;

        foreach ($files as $filePath) {
            $this->newLine();
            $this->info("⏳ Processing file: " . basename($filePath) . " ...");

            $handle = fopen($filePath, 'r');
            if (!$handle) {
                $this->warn("⚠️ Cannot open file: {$filePath}");
                continue;
            }

            $batchSize = 250;
            $lineCount = 0;

            DB::beginTransaction();

            while (($line = fgets($handle)) !== false) {
                $line = trim($line);
                if (empty($line)) continue;

                $row = json_decode($line, true);
                if (!$row) {
                    $totalSkipped++;
                    continue;
                }

                $res = $harvester->ingestRecord($row);
                if ($res['status'] === 'created') $totalCreated++;
                elseif ($res['status'] === 'duplicate') $totalDuplicates++;
                else $totalSkipped++;

                $lineCount++;

                if ($lineCount % $batchSize === 0) {
                    DB::commit();
                    DB::beginTransaction();
                    $this->output->write("  ... Processed {$lineCount} rows (Created: {$totalCreated}, Duplicates: {$totalDuplicates})\r");
                }
            }

            DB::commit();
            fclose($handle);

            $this->newLine();
            $this->info("✅ Finished " . basename($filePath) . " ({$lineCount} rows)");
        }

        $this->newLine();
        $this->table(['Status', 'Count'], [
            ['Total Created (New Fatwas)', $totalCreated],
            ['Total Duplicates Filtered', $totalDuplicates],
            ['Total Skipped / Invalid', $totalSkipped],
            ['Total Sharia Records in DB', ShariaRecord::count()],
        ]);

        return 0;
    }
}
