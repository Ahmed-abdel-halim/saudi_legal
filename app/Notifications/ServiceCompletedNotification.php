<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ServiceCompletedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public $contract)
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
                    ->subject('Service Completed: Funds Released')
                    ->line('The company has confirmed completion of the service.')
                    ->line('Payment will be processed shortly.')
                    ->action('View Contract', $this->getChatUrl($notifiable))
                    ->line('Great work!');
    }

    public function toArray($notifiable): array
    {
        return [
            'id' => $this->id,
            'title' => 'Service Completed',
            'message' => 'The company confirmed completion. Payment released.',
            'contract_id' => $this->contract->id,
            'contract_type' => $this->contract instanceof \App\Models\ProjectOffer ? 'offer' : 'hourly_purchase',
            'url' => $this->getChatUrl($notifiable),
            'timestamp' => now()->toIso8601String(),
        ];
    }

    protected function getChatUrl($user): string
    {
        // Expert view
        return route('dashboard.expert.chat.show', $this->contract->conversation->id);
    }
}
