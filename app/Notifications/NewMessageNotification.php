<?php

namespace App\Notifications;

use App\Models\Message;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class NewMessageNotification extends Notification implements ShouldBroadcast
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public Message $message)
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $url = $this->getChatUrl($notifiable);
        
        return (new MailMessage)
                    ->line('You have a new message.')
                    ->action('View Chat', $url)
                    ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'id' => $this->message->id,
            'title' => 'New Message',
            'message' => Str::limit($this->message->content, 50),
            'chat_id' => $this->message->conversation_id,
            'url' => $this->getChatUrl($notifiable),
            'sender_id' => $this->message->sender_id,
            'sender_name' => $this->message->sender ? $this->message->sender->name : 'System',
            'created_at' => $this->message->created_at->toIso8601String(),
        ];
    }
    
    protected function getChatUrl($user): string
    {
        if ($user->role === 'expert') {
            return route('dashboard.expert.chat.show', $this->message->conversation_id);
        }
        
        return route('dashboard.chat.show', $this->message->conversation_id);
    }
    
    /**
     * Get the broadcastable representation of the notification.
     */
    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'message' => $this->toArray($notifiable),
        ]);
    }
}
