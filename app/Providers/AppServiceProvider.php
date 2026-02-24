<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
use App\Models\User;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Paginator::useTailwind();

        \Illuminate\Validation\Rules\Password::defaults(function () {
            return \Illuminate\Validation\Rules\Password::min(8);
        });

        // Register governance event listeners
        \Illuminate\Support\Facades\Event::listen(
            \App\Events\AnswerSubmitted::class,
            [\App\Listeners\ValidateGoldStandard::class, 'handle']
        );

        \Illuminate\Support\Facades\Event::listen(
            \App\Events\AnswerSubmitted::class,
            [\App\Listeners\EvaluateConsensus::class, 'handle']
        );

        // ── Admin Gate Definitions ─────────────────────────────────────────
        // Allow both 'admin' and 'superadmin' for all admin gates
        $isAdmin = fn(User $user) => in_array($user->role, ['admin', 'superadmin']);

        Gate::define('resolveDisputes',        $isAdmin);
        Gate::define('viewDashboard',          $isAdmin);
        Gate::define('viewAllConversations',   $isAdmin);
        Gate::define('sendSystemMessages',     $isAdmin);
        Gate::define('manageUsers',            $isAdmin);
        Gate::define('viewReports',            $isAdmin);
    }
}
