<?php

use App\Services\MediaService;
use App\Support\Branding;

if (! function_exists('media_url')) {
    /**
     * Public URL for a stored upload.
     *
     * Handles both the current pipeline (public/upload/...) and files saved on
     * the `public` storage disk before it existed, so views never need to know
     * which era a record comes from.
     */
    function media_url(?string $path, bool $thumb = false): string
    {
        return app(MediaService::class)->url($path, $thumb);
    }
}

if (! function_exists('money')) {
    /**
     * Format an amount for display with the configured currency symbol.
     *
     * Every money value in the panel goes through here so the symbol, decimal
     * places and negative-number style are decided in one place.
     *
     * Pass `$withSymbol = false` inside generated PDFs: DomPDF's bundled font
     * has no glyph for several currency symbols, so documents print the
     * currency once in the header and the bare number on each row.
     */
    function money(int|float|string|null $amount, bool $withSymbol = true): string
    {
        $value = (float) ($amount ?? 0);
        $formatted = number_format(abs($value), 2);

        if ($withSymbol) {
            $formatted = trim(Branding::currency()).' '.$formatted;
        }

        return $value < 0 ? '-'.$formatted : $formatted;
    }
}

if (! function_exists('qty')) {
    /**
     * Format a quantity, dropping the decimals when the value is whole.
     * Stock is stored as decimal(…, 2) but is usually counted in whole units.
     */
    function qty(int|float|string|null $quantity): string
    {
        $value = (float) ($quantity ?? 0);

        return fmod($value, 1.0) === 0.0
            ? number_format($value)
            : rtrim(rtrim(number_format($value, 2), '0'), '.');
    }
}

if (! function_exists('amount_in_words')) {
    /**
     * Spell an amount out for the "in words" line on an invoice.
     *
     * Uses the international scale (thousand/million) rather than the South
     * Asian lakh/crore scale, matching the digit grouping used elsewhere in
     * the panel.
     */
    function amount_in_words(int|float|string|null $amount): string
    {
        $value = round((float) ($amount ?? 0), 2);
        $whole = (int) floor(abs($value));
        $paisa = (int) round((abs($value) - $whole) * 100);

        $formatter = new NumberFormatter('en', NumberFormatter::SPELLOUT);

        $words = ucfirst($formatter->format($whole));

        if ($paisa > 0) {
            $words .= ' and '.$formatter->format($paisa).' paisa';
        }

        return ($value < 0 ? 'Minus ' : '').$words.' only';
    }
}

if (! function_exists('percent')) {
    /**
     * Format a percentage without trailing zeros ("12.5%", "10%").
     */
    function percent(int|float|string|null $value, int $decimals = 2): string
    {
        $formatted = number_format((float) ($value ?? 0), $decimals);

        return rtrim(rtrim($formatted, '0'), '.').'%';
    }
}
