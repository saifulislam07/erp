<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

#[Signature('erp:backup')]
#[Description('Export critical tables to JSON files under storage/app/private/backups/{date}/')]
class ErpBackupCommand extends Command
{
    private const array TABLES = [
        'clients',
        'products',
        'categories',
        'suppliers',
        'stocks',
        'orders',
        'order_items',
        'sales',
        'sale_items',
        'purchases',
        'purchase_items',
        'expenses',
        'accounts',
        'assets',
        'settings',
    ];

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $date = now()->format('Y-m-d_His');
        $directory = "backups/{$date}";

        Storage::makeDirectory($directory);

        foreach (self::TABLES as $table) {
            if (! DB::getSchemaBuilder()->hasTable($table)) {
                continue;
            }

            $rows = DB::table($table)->get();

            Storage::put(
                "{$directory}/{$table}.json",
                $rows->toJson(JSON_PRETTY_PRINT)
            );

            $this->info("Backed up {$table} ({$rows->count()} rows).");
        }

        $this->info('Backup completed: '.Storage::path($directory));

        return self::SUCCESS;
    }
}
