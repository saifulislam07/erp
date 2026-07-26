<?php

use Illuminate\Database\Eloquent\Builder;

if (! function_exists('generateUniqueId')) {
    /**
     * Generate a unique ID in the format YYMM + 4-digit serial, incrementing
     * per calendar month for the given model/column.
     */
    function generateUniqueId(string $modelClass, string $prefix = '', string $column = 'unique_id'): string
    {
        $yearMonth = now()->format('ym');
        $base = $prefix.$yearMonth;

        /** @var Builder $query */
        $query = $modelClass::query();

        if (method_exists($modelClass, 'bootSoftDeletes')) {
            $query->withTrashed();
        }

        $lastId = $query
            ->where($column, 'like', $base.'%')
            ->orderByDesc($column)
            ->value($column);

        $serial = $lastId ? ((int) substr($lastId, strlen($base)) + 1) : 1;

        return $base.str_pad((string) $serial, 4, '0', STR_PAD_LEFT);
    }
}
