<?php

namespace App\Policies;

use App\Models\ProjectOffer;
use App\Models\User;

class ProjectOfferPolicy
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
    public function view(User $user, ProjectOffer $offer): bool
    {
        // Company can view their own project's offers
        // Expert can view their own offers
        return $offer->expert_id === $user->id 
            || $offer->project->requester_company_id === $user->company_id
            || $user->role === 'admin';
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Only experts can create offers
        return $user->role === 'expert';
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ProjectOffer $offer): bool
    {
        // Only the expert who created the offer can update it
        // And only if it's still pending
        return $offer->expert_id === $user->id 
            && $offer->status === 'pending';
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ProjectOffer $offer): bool
    {
        // Expert can withdraw their own pending offers
        return $offer->expert_id === $user->id 
            && $offer->status === 'pending';
    }

    /**
     * Determine whether the user can accept the offer.
     */
    public function accept(User $user, ProjectOffer $offer): bool
    {
        // Only the company that owns the project can accept
        return $offer->project->requester_company_id === $user->company_id
            && $offer->status === 'pending';
    }
}
