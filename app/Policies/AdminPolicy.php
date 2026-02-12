<?php

namespace App\Policies;

use App\Models\User;

class AdminPolicy
{
    /**
     * Determine if the user can access admin dashboard.
     */
    public function viewDashboard(User $user): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Determine if the user can view all conversations.
     */
    public function viewAllConversations(User $user): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Determine if the user can resolve disputes.
     */
    public function resolveDisputes(User $user): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Determine if the user can send system messages.
     */
    public function sendSystemMessages(User $user): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Determine if the user can manage users.
     */
    public function manageUsers(User $user): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Determine if the user can view reports.
     */
    public function viewReports(User $user): bool
    {
        return $user->role === 'admin';
    }
}
