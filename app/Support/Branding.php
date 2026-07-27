<?php

namespace App\Support;

use App\Models\Setting;
use Illuminate\Support\Str;
use Throwable;

/**
 * Resolves the panel's visual identity (name, logo, initials) from the
 * Settings module, falling back to config values.
 *
 * Every reader is guarded: branding is rendered on the error pages too, which
 * must keep working when the database is unreachable.
 */
class Branding
{
    /**
     * Company / application name shown in the sidebar, titles and documents.
     */
    public static function name(): string
    {
        return self::setting('company_name') ?: (string) config('app.name', 'ERP');
    }

    /**
     * Short name for tight spaces (sidebar collapsed, browser tab).
     */
    public static function shortName(): string
    {
        return Str::limit(self::name(), 22, '');
    }

    /**
     * Public URL of the uploaded company logo, or null when none is set.
     */
    public static function logoUrl(): ?string
    {
        $path = self::setting('company_logo');

        if (! $path) {
            return null;
        }

        // Logos uploaded through the media pipeline live under public/upload;
        // ones stored before that still live on the `public` disk.
        if (str_starts_with($path, 'upload/')) {
            return asset($path);
        }

        return asset('storage/'.$path);
    }

    /**
     * Two-letter monogram used when no logo image is configured.
     */
    public static function monogram(): string
    {
        $words = preg_split('/\s+/', trim(self::name())) ?: [];
        $words = array_values(array_filter($words));

        if ($words === []) {
            return 'ER';
        }

        if (count($words) === 1) {
            return Str::upper(Str::substr($words[0], 0, 2));
        }

        return Str::upper(Str::substr($words[0], 0, 1).Str::substr($words[1], 0, 1));
    }

    /**
     * Currency symbol used by every money formatter in the panel.
     */
    public static function currency(): string
    {
        return self::setting('currency_symbol') ?: (string) config('erp.currency_symbol', '৳');
    }

    private static function setting(string $key): ?string
    {
        try {
            $value = Setting::get($key);
        } catch (Throwable) {
            // No database yet (install, migration, or an outage on an error page).
            return null;
        }

        return is_string($value) && $value !== '' ? $value : null;
    }
}
