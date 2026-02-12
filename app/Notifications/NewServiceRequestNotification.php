<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class NewServiceRequestNotification extends Notification implements ShouldQueue, ShouldBroadcast
{
    use Queueable;

    public function __construct(public $purchase)
    {
    }

    public function via($notifiable): array
    {
        return ['database', 'mail', 'broadcast'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
                    ->subject('New Service Request Received')
                    ->line('You have received a new service request.')
                    ->line('Service: ' . ($this->purchase->service->title ?? 'Service'))
                    ->line('Hours: ' . $this->purchase->hours_purchased)
                    ->action('View Dashboard', route('dashboard.expert'))
                    ->line('Please accept or reject the request from your dashboard.');
    }

    public function toArray($notifiable): array
    {
        return [
            'id' => $this->purchase->id,
            'title' => 'New Request: ' . ($this->purchase->service->title ?? 'Service'),
            'message' => 'New request for ' . $this->purchase->hours_purchased . ' hours.',
            'type' => 'Service', // For icon logic
            'url' => route('dashboard.expert'), // Link to dashboard where user can accept
            'created_at' => $this->purchase->created_at->toIso8601String(),
        ];
    }

    public function toBroadcast($notifiable): BroadcastMessage
    {
        // Load relationships if not already loaded
        $this->purchase->load(['client', 'service']);

        return new BroadcastMessage([
            'id' => 'req_' . $this->purchase->id,
            'source_id' => $this->purchase->id,
            'type' => 'NewServiceRequestNotification',
            'data' => [
                'id' => $this->purchase->id,
                'title' => 'New Request: ' . (optional($this->purchase->service)->title ?? 'Service'),
                'message' => 'New request from ' . (optional($this->purchase->client)->name ?? 'Client') . ' for ' . $this->purchase->hours_purchased . ' hours.',
                'url' => route('dashboard.expert'),
                'client_name' => optional($this->purchase->client)->full_name ?? optional($this->purchase->client)->name ?? 'Client',
                'client_avatar' => optional($this->purchase->client)->avatar_path ?? null,
                'service_title' => optional($this->purchase->service)->title ?? 'Service',
                'hours' => $this->purchase->hours_purchased,
                'request_id' => $this->purchase->id,
                'created_at_human' => $this->purchase->created_at ? $this->purchase->created_at->diffForHumans() : 'Just now',
            ],
            'read_at' => null,
            'created_at' => $this->purchase->created_at ? $this->purchase->created_at->diffForHumans() : 'Just now',
            'timestamp' => $this->purchase->created_at ? $this->purchase->created_at->timestamp : time(),
        ]);
    }

    public function broadcastType(): string
    {
        return 'notification.new';
    }
}
