<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderStatusChangedNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly Order $order, private readonly ?string $extraNote = null)
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

        if (config('erp.notifications.order_status_change', true)) {
            $channels[] = 'mail';
        }

        return $channels;
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject("Order {$this->order->order_id} Update")
            ->line("Your order {$this->order->order_id} status has changed to: ".ucfirst(str_replace('_', ' ', $this->order->status)));

        if ($this->extraNote) {
            $mail->line($this->extraNote);
        }

        return $mail->action('View Order', route('client.orders.show', $this->order));
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => "Order {$this->order->order_id} Update",
            'message' => 'Status changed to: '.ucfirst(str_replace('_', ' ', $this->order->status)),
            'url' => route('client.orders.show', $this->order),
        ];
    }
}
