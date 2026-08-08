<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;

class GenerateSitemap extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sitemap:generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate the custom B2B SaaS sitemap';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Generating B2B SaaS sitemap...');

        $baseUrl = rtrim(config('app.sitemap_domain', env('SITEMAP_BASE_URL', 'https://radiif.com')), '/');

        // Create the sitemap with specific pages to present a premium B2B SaaS positioning
        Sitemap::create()
            ->add(Url::create($baseUrl . '/')
                ->setPriority(1.0)
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY))
            ->add(Url::create($baseUrl . '/api-docs')
                ->setPriority(0.8)
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY))
            ->add(Url::create($baseUrl . '/security-compliance')
                ->setPriority(0.8)
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY))
            ->add(Url::create($baseUrl . '/services/rlhf')
                ->setPriority(0.9)
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY))
            ->add(Url::create($baseUrl . '/services/hitl')
                ->setPriority(0.9)
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY))
            ->add(Url::create($baseUrl . '/services/data-infrastructure')
                ->setPriority(0.9)
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY))
            ->writeToFile(public_path('sitemap.xml'));

        $this->info('Sitemap generated successfully at: ' . public_path('sitemap.xml'));
        return self::SUCCESS;
    }
}
