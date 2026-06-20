<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
use App\Models\User;
use App\Models\LegalQaPair;
use App\Observers\LegalQaPairObserver;

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

        // ── Azure Auto-Indexing Observers ──────────────────────────────────
        // يفهرس QA Pairs في Azure Search تلقائياً عند الموافقة عليها
        LegalQaPair::observe(LegalQaPairObserver::class);



        // ── Admin Gate Definitions ─────────────────────────────────────────
        $isAdmin = fn(User $user) => in_array($user->role, ['admin', 'superadmin']);

        Gate::define('resolveDisputes',        $isAdmin);
        Gate::define('viewDashboard',          $isAdmin);
        Gate::define('viewAllConversations',   $isAdmin);
        Gate::define('sendSystemMessages',     $isAdmin);
        Gate::define('manageUsers',            $isAdmin);
        Gate::define('viewReports',            $isAdmin);
    }
}
