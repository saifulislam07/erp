<?php

namespace App\Notifications;

use App\Support\Branding;
use App\Support\MailSettings;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * The "forgot my password" email for staff accounts.
 *
 * Replaces the framework's default so the message carries the company name and
 * can be switched off from Settings.
 */
class StaffPasswordResetNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly string $token) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return MailSettings::notificationEnabled('mail_send_password_reset') ? ['mail'] : [];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $company = Branding::name();
        $minutes = config('auth.passwords.'.config('auth.defaults.passwords').'.expire', 60);

        $url = url(route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], absolute: false));

        return (new MailMessage)
            ->subject("Reset your {$company} password")
            ->greeting('Hello '.($notifiable->name ?? '').',')
            ->line("We received a request to reset the password for your {$company} account.")
            ->action('Choose a new password', url($url))
            ->line("This link stops working in {$minutes} minutes.")
            ->line('If you did not ask for this, you can ignore the email — your password stays as it is.')
            ->salutation("— {$company}");
    }
}
