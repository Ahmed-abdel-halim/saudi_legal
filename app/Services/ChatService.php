<?php

namespace App\Services;

use App\Events\NewMessageEvent;
use App\Events\ChatCreatedEvent;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\ProjectOffer;
use App\Models\ServicePurchase;
use App\Models\User;
use App\Notifications\NewMessageNotification;
use App\Notifications\ChatCreatedNotification;
use Illuminate\Support\Facades\DB;

class ChatService
{
    /**
     * Create or retrieve conversation for an offer
     * Implements: One offer = One conversation (lifetime)
     */
    public function createChatForOffer(ProjectOffer $offer): Conversation
    {
        // Check for existing conversation
        $existing = Conversation::where('contract_type', Conversation::TYPE_OFFER)
            ->where('contract_id', $offer->id)
            ->first();
        
        if ($existing) {
            return $existing;
        }
        
        // Create new conversation
        $conversation = Conversation::create([
            'contract_type' => Conversation::TYPE_OFFER,
            'contract_id' => $offer->id,
            'participant_1' => $offer->project->company->user_id,
            'participant_2' => $offer->expert_id,
            'status' => Conversation::STATUS_ACTIVE,
            'company_last_read_at' => now(), // Initial read
            'expert_last_read_at' => null,   // Expert hasn't seen it yet
        ]);
        
        // Notify participants
        $this->notifyChatCreated($conversation);
        
        // Create system message
        $this->createSystemMessage(
            $conversation,
            "Offer accepted. Service agreement initiated."
        );
        
        return $conversation;
    }
    
    /**
     * Create or retrieve conversation for a purchase
     */
    public function createChatForPurchase(ServicePurchase $purchase): Conversation
    {
        $existing = Conversation::where('contract_type', Conversation::TYPE_HOURLY_PURCHASE)
            ->where('contract_id', $purchase->id)
            ->first();
        
        if ($existing) {
            return $existing;
        }
        
        $conversation = Conversation::create([
            'contract_type' => Conversation::TYPE_HOURLY_PURCHASE,
            'contract_id' => $purchase->id,
            'participant_1' => $purchase->client_id,
            'participant_2' => $purchase->expert_id,
            'status' => Conversation::STATUS_ACTIVE,
            'company_last_read_at' => now(),
            'expert_last_read_at' => null,
        ]);
        
        $this->notifyChatCreated($conversation);

        $this->createSystemMessage(
            $conversation,
            "Purchase accepted. {$purchase->hours_purchased} hours of service initiated."
        );
        
        return $conversation;
    }
    
    protected function notifyChatCreated(Conversation $conversation)
    {
        broadcast(new ChatCreatedEvent($conversation));
        
        // Send notification to both participants
        // Implement ChatCreatedNotification later or trigger here
        // User::find($conversation->participant_1)->notify(new ChatCreatedNotification($conversation));
        // User::find($conversation->participant_2)->notify(new ChatCreatedNotification($conversation));
    }

    /**
     * Send message with presence-aware notification
     * Only notifies if receiver is NOT currently viewing the chat
     */
    public function sendMessage(
        Conversation $conversation,
        int $senderId,
        string $content,
        bool $receiverIsPresent = false
    ): Message {
        $message = Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $senderId,
            'sender_type' => $this->determineSenderType($senderId),
            'content' => $content,
        ]);
        
        // Update unread count by NOT updating the receiver's read timestamp yet set in conversation (handled on view)
        // But verify if marking as read for sender
        $conversation->markAsReadBy($senderId);

        // Broadcast message event (always)
        broadcast(new NewMessageEvent($message));
        
        // Send notification ONLY if receiver is not present
        if (!$receiverIsPresent) {
            $receiverId = $conversation->getOtherParticipant($senderId);
            User::find($receiverId)->notify(
                new NewMessageNotification($message)
            );
        }
        
        return $message;
    }
    
    /**
     * Create system message for audit trail
     */
    public function createSystemMessage(Conversation $conversation, string $content): Message
    {
        $message = Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => null,
            'sender_type' => Message::TYPE_SYSTEM,
            'content' => $content,
        ]);
        
        broadcast(new NewMessageEvent($message));
        
        return $message;
    }
    
    private function determineSenderType(int $userId): string
    {
        $user = User::find($userId);
        // This assumes 'role' field exists and is correct string
        // Adjust if role logic is more complex
        if ($user->role === 'expert') {
            return Message::TYPE_EXPERT;
        }
        return Message::TYPE_COMPANY;
    }
}
