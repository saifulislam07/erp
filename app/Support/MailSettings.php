<?php

namespace App\Support;

use App\Models\Setting;
use Illuminate\Support\Facades\Config;
use Throwable;

/**
 * Applies the SMTP details stored in the Settings module to the runtime mail
 * configuration.
 *
 * Keeping them in the database means an administrator can point the system at a
 * different mail server from the UI. Anything left blank falls back to the
 * value already in config/.env, so an installation that prefers to configure
 * mail through .env keeps working untouched.
 */
class MailSettings
{
    public static function apply(): void
    {
        try {
            $settings = Setting::allCached();
        } catch (Throwable) {
            // No database yet (install or migration) — leave config alone.
            return;
        }

        $mailer = self::value($settings, 'mail_mailer') ?? config('mail.default');

        Config::set('mail.default', $mailer);

        // Only the SMTP transport reads host/port/credentials.
        if ($mailer === 'smtp') {
            $map = [
                'mail.mailers.smtp.host' => 'mail_host',
                'mail.mailers.smtp.port' => 'mail_port',
                'mail.mailers.smtp.username' => 'mail_username',
                'mail.mailers.smtp.password' => 'mail_password',
                'mail.mailers.smtp.encryption' => 'mail_encryption',
            ];

            foreach ($map as $configKey => $settingKey) {
                $value = self::value($settings, $settingKey);

                if ($value !== null) {
                    Config::set($configKey, $settingKey === 'mail_port' ? (int) $value : $value);
                }
            }

            // "none" is how the form expresses "no TLS"; the transport wants null.
            if (self::value($settings, 'mail_encryption') === 'none') {
                Config::set('mail.mailers.smtp.encryption', null);
            }
        }

        if ($from = self::value($settings, 'mail_from_address')) {
            Config::set('mail.from.address', $from);
        }

        $fromName = self::value($settings, 'mail_from_name') ?? self::value($settings, 'company_name');

        if ($fromName) {
            Config::set('mail.from.name', $fromName);
        }
    }

    /**
     * Whether a given automatic email is switched on.
     *
     * Defaults to enabled so a fresh installation behaves as expected before
     * anyone visits the settings screen.
     */
    public static function notificationEnabled(string $key): bool
    {
        try {
            $value = Setting::get($key);
        } catch (Throwable) {
            return false;
        }

        return $value === null ? true : (bool) $value;
    }

    /**
     * @param  array<string, mixed>  $settings
     */
    private static function value(array $settings, string $key): ?string
    {
        $value = $settings[$key] ?? null;

        return is_string($value) && $value !== '' ? $value : null;
    }
}
