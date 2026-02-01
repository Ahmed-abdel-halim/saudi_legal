<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use Illuminate\Pagination\Paginator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useTailwind();

        // Register governance event listeners
        \Illuminate\Support\Facades\Event::listen(
            \App\Events\AnswerSubmitted::class,
            [\App\Listeners\ValidateGoldStandard::class, 'handle']
        );

        \Illuminate\Support\Facades\Event::listen(
            \App\Events\AnswerSubmitted::class,
            [\App\Listeners\EvaluateConsensus::class, 'handle']
        );
    }
}
