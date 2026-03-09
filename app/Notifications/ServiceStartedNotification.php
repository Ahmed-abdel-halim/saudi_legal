<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ServiceStartedNotification extends Notification implements ShouldQueue
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
                    ->subject('Service Started: ' . $this->contract->project->title ?? 'Consultation')
                    ->line('The expert has started working on your service.')
                    ->action('View Progress', $this->getChatUrl($notifiable))
                    ->line('Thank you for using our platform!');
    }

    public function toArray($notifiable): array
    {
        return [
            'title' => 'Service Started',
            'message' => 'The expert has started working on the service.',
            'contract_id' => $this->contract->id,
            'contract_type' => $this->contract instanceof \App\Models\ProjectOffer ? 'offer' : 'hourly_purchase',
            'url' => $this->getChatUrl($notifiable),
        ];
    }

    protected function getChatUrl($user): string
    {
        // Company view
        return route('dashboard.chat.show', $this->contract->conversation->id);
    }
}
