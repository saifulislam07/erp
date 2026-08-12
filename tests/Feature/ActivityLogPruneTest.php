<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

/**
 * Nothing else trims `activity_log`, so the scheduled `activitylog:clean` is the
 * only thing standing between a busy install and an unbounded table.
 */
class ActivityLogPruneTest extends TestCase
{
    use RefreshDatabase;

    public function test_records_older_than_the_retention_window_are_deleted(): void
    {
        config(['activitylog.delete_records_older_than_days' => 30]);

        $stale = activity()->log('stale');
        $stale->forceFill(['created_at' => now()->subDays(45)])->save();

        $recent = activity()->log('recent');
        $recent->forceFill(['created_at' => now()->subDays(10)])->save();

        $this->artisan('activitylog:clean')->assertSuccessful();

        $this->assertNull(Activity::find($stale->id));
        $this->assertNotNull(Activity::find($recent->id));
    }

    public function test_retention_window_is_configurable(): void
    {
        config(['activitylog.delete_records_older_than_days' => 400]);

        $activity = activity()->log('a year old');
        $activity->forceFill(['created_at' => now()->subDays(370)])->save();

        $this->artisan('activitylog:clean')->assertSuccessful();

        $this->assertNotNull(Activity::find($activity->id));
    }
}
