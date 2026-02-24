<?php

namespace App\Policies;

use App\Models\User;

class AdminPolicy
{
    /**
     * Check if the user is an admin or superadmin.
     */
    private function isAdmin(User $user): bool
    {
        return in_array($user->role, ['admin', 'superadmin']);
    }

    public function viewDashboard(User $user): bool
    {
        return $this->isAdmin($user);
    }

    public function viewAllConversations(User $user): bool
    {
        return $this->isAdmin($user);
    }

    public function resolveDisputes(User $user): bool
    {
        return $this->isAdmin($user);
    }

    public function sendSystemMessages(User $user): bool
    {
        return $this->isAdmin($user);
    }

    public function manageUsers(User $user): bool
    {
        return $this->isAdmin($user);
    }

    public function viewReports(User $user): bool
    {
        return $this->isAdmin($user);
    }
}
