<?php

namespace App\Policies;

use App\Models\Message;
use App\Models\User;

class MessagePolicy
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
    public function view(User $user, Message $message): bool
    {
        // User must be a participant in the conversation
        return $message->conversation->isParticipant($user->id)
            || $user->role === 'admin';
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true; // Checked in controller with conversation policy
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Message $message): bool
    {
        // Only sender can edit their own messages (within time limit)
        // System messages cannot be edited
        return $message->sender_id === $user->id 
            && !$message->isSystemMessage()
            && $message->created_at->diffInMinutes(now()) < 15;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Message $message): bool
    {
        // Only sender or admin can delete
        return $message->sender_id === $user->id 
            || $user->role === 'admin';
    }
}
