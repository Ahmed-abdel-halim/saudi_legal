<?php

namespace App\Console\Commands;

use App\Services\RoutingService;
use Illuminate\Console\Command;

class ReleaseExpiredAssignments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tasks:release-expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Release expired task assignments and make them available again';

    /**
     * Execute the console command.
     */
    public function handle(RoutingService $routingService): int
    {
        $this->info('Releasing expired assignments...');

        $count = $routingService->releaseExpiredAssignments();

        $this->info("Released {$count} expired assignment(s).");

        return Command::SUCCESS;
    }
}
