<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DisputeOpenedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public $contract, public $reason)
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
                    ->subject('New Dispute Opened: ' . $this->contract->id)
                    ->line('A dispute has been opened for Contract #' . $this->contract->id)
                    ->line('Reason: ' . $this->reason)
                    ->action('View Dispute', url('/admin/disputes/' . $this->contract->id))
                    ->line('Please review ASAP.');
    }

    public function toArray($notifiable): array
    {
        return [
            'title' => 'Dispute Opened',
            'message' => 'Dispute on Contract #' . $this->contract->id . ': ' . $this->reason,
            'contract_id' => $this->contract->id,
            'contract_type' => $this->contract instanceof \App\Models\ProjectOffer ? 'offer' : 'hourly_purchase',
            'reason' => $this->reason,
            'url' => url('/admin/disputes/' . $this->contract->id),
        ];
    }
}
