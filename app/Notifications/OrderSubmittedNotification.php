<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderSubmittedNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly Order $order)
    {
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        $channels = ['database'];

        if (config('erp.notifications.order_placed', true)) {
            $channels[] = 'mail';
        }

        return $channels;
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("New Order Submitted: {$this->order->order_id}")
            ->line("A new order {$this->order->order_id} has been submitted by {$this->order->client->name}.")
            ->line("Total amount: {$this->order->total_amount}")
            ->action('Review Order', route('admin.orders.show', $this->order));
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => "New Order: {$this->order->order_id}",
            'message' => "{$this->order->client->name} submitted a new order worth {$this->order->total_amount}.",
            'url' => route('admin.orders.show', $this->order),
        ];
    }
}
