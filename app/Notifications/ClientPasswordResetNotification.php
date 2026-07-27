<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ClientPasswordResetNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly string $newPassword)
    {
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return config('erp.notifications.password_reset', true) ? ['mail'] : [];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your Password Has Been Reset')
            ->line('An administrator has reset your account password.')
            ->line('Your new password is: '.$this->newPassword)
            ->line('Please log in and change your password as soon as possible.')
            ->action('Login', route('client.login'));
    }
}
