<?php

namespace App\Notifications;

use App\Models\Message;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class MessageReceivedNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly Message $message, private readonly string $senderName)
    {
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $url = $this->message->receiver_type === 'client'
            ? route('client.messages.index')
            : route('admin.messages.index', ['client' => $this->message->sender_id]);

        return [
            'title' => "New message from {$this->senderName}",
            'message' => Str::limit($this->message->message, 80),
            'url' => $url,
        ];
    }
}
