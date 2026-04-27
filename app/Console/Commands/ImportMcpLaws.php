<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\LegalArticle;
use Illuminate\Support\Facades\File;

class ImportMcpLaws extends Command
{
    protected $signature = 'import:mcp-laws';
    protected $description = 'Import Saudi laws and articles from MCP JSON files';

    public function handle()
    {
        $directory = base_path('Saudi-law-mcp-main/data/seed');

        if (!File::exists($directory)) {
            $this->error("Directory not found: {$directory}");
            return;
        }

        $files = File::files($directory);
        $this->info("Found " . count($files) . " JSON files. Starting import...");

        $count = 0;
        foreach ($files as $file) {
            if ($file->getExtension() !== 'json') continue;

            $content = File::get($file->getPathname());
            $data = json_decode($content, true);

            if (!$data || !isset($data['provisions'])) continue;

            $legislationTitle = $data['title'];
            $legislationId = $data['id'];

            foreach ($data['provisions'] as $provision) {
                LegalArticle::updateOrCreate(
                    [
                        'legislation_id' => $legislationId,
                        'article_title' => $provision['title'],
                    ],
                    [
                        'legislation_title' => $legislationTitle,
                        'content' => $provision['content'],
                        'reference_id' => $provision['provision_ref'] ?? null,
                    ]
                );
                $count++;
            }

            $this->info("Imported: {$legislationTitle}");
        }

        $this->info("Import completed successfully! Total articles: {$count}");
    }
}
