<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The unit symbol only ever duplicated the unit name where it was displayed
 * ("Kilogram (kg)"), so the name is now the single label for a unit.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('units', 'symbol')) {
            Schema::table('units', function (Blueprint $table) {
                $table->dropColumn('symbol');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasColumn('units', 'symbol')) {
            Schema::table('units', function (Blueprint $table) {
                $table->string('symbol')->default('')->after('name');
            });
        }
    }
};
