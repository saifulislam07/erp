<?php

namespace Tests\Unit;

use Tests\TestCase;

/**
 * The display helpers every screen and PDF funnels numbers through. Only the
 * ones that need no database live here; `money()` reads the configured
 * currency symbol and is covered in the feature suite.
 */
class FormatHelperTest extends TestCase
{
    public function test_whole_quantities_drop_their_decimals(): void
    {
        $this->assertSame('12', qty(12));
        $this->assertSame('12', qty('12.00'));
        $this->assertSame('1,200', qty(1200));
    }

    public function test_fractional_quantities_keep_only_the_digits_that_matter(): void
    {
        $this->assertSame('12.5', qty(12.5));
        $this->assertSame('12.25', qty(12.25));
    }

    public function test_null_quantity_reads_as_zero(): void
    {
        $this->assertSame('0', qty(null));
    }

    public function test_percentages_lose_their_trailing_zeros(): void
    {
        $this->assertSame('10%', percent(10));
        $this->assertSame('12.5%', percent(12.5));
        $this->assertSame('0%', percent(null));
    }

    public function test_amounts_are_spelled_out_for_invoices(): void
    {
        $this->assertSame('One thousand five hundred only', amount_in_words(1500));
    }

    public function test_spelled_out_amounts_include_paisa(): void
    {
        $this->assertSame('Ten and fifty paisa only', amount_in_words(10.50));
    }

    public function test_negative_amounts_are_spelled_out_as_minus(): void
    {
        $this->assertStringStartsWith('Minus ', amount_in_words(-25));
    }
}
