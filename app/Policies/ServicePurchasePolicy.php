<?php

namespace App\Policies;

use App\Models\ServicePurchase;
use App\Models\User;

class ServicePurchasePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, ServicePurchase $purchase): bool
    {
        // Client or expert involved can view
        return $purchase->client_id === $user->id 
            || $purchase->expert_id === $user->id
            || $user->role === 'admin';
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Only companies can purchase services
        return $user->role === 'company';
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ServicePurchase $purchase): bool
    {
        // Only admin can directly update
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ServicePurchase $purchase): bool
    {
        // Client can cancel pending purchases
        return $purchase->client_id === $user->id 
            && $purchase->status === 'pending';
    }

    /**
     * Determine whether the user can accept the purchase.
     */
    public function accept(User $user, ServicePurchase $purchase): bool
    {
        // Only the expert can accept
        return $purchase->expert_id === $user->id
            && $purchase->status === 'pending';
    }
}
