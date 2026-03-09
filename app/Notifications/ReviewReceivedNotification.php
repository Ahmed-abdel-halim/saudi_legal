<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReviewReceivedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public $review)
    {
    }

    public function via($notifiable): array
    {
        $channels = ['database'];
        if (config('mail.from.address') && config('mail.from.address') !== 'hello@example.com') {
            $channels[] = 'mail';
        }
        return $channels;
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
                    ->subject('New Review Received: ' . $this->review->rating . ' Stars')
                    ->line('You have received a new review for your recent service.')
                    ->line('Rating: ' . $this->review->rating . '/5')
                    ->line('Comment: ' . $this->review->comment)
                    ->action('View Profile', url('/dashboard/expert/analytics')) // Assuming analytics or profile page
                    ->line('Keep up the great work!');
    }

    public function toArray($notifiable): array
    {
        // Find associated conversation
        $conversation = \App\Models\Conversation::where('contract_id', $this->review->contract_id)
            ->where('contract_type', $this->review->contract_type)
            ->first();

        return [
            'id' => $this->review->id,
            'title' => 'New Review Received',
            'message' => 'You received a ' . $this->review->rating . '-star review.',
            'rating' => $this->review->rating,
            'comment' => $this->review->comment,
            'url' => $conversation ? route('dashboard.expert.chat.show', $conversation->id) : '#',
            'created_at' => $this->review->created_at->toIso8601String(),
        ];
    }
}
