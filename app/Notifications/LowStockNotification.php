<?php

namespace App\Notifications;

use App\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class LowStockNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly Product $product, private readonly float $availableStock)
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
        return [
            'title' => "Low Stock: {$this->product->name}",
            'message' => "Available stock ({$this->availableStock}) is below the minimum threshold ({$this->product->min_stock_threshold}).",
            'url' => route('admin.stocks.low-quantity'),
        ];
    }
}
