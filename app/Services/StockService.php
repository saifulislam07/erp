<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Stock;
use App\Models\StockMovement;
use App\Models\User;
use App\Notifications\LowStockNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use RuntimeException;

class StockService
{
    public function addStock(
        int $productId,
        int $storeId,
        float $quantity,
        float $purchasePrice,
        ?string $expiryDate,
        ?string $referenceType,
        ?int $referenceId,
        int $createdBy,
        ?string $note = null,
        ?string $batchNumber = null,
    ): Stock {
        return DB::transaction(function () use ($productId, $storeId, $quantity, $purchasePrice, $expiryDate, $referenceType, $referenceId, $createdBy, $note, $batchNumber) {
            $before = $this->getAvailableStock($productId, $storeId);

            $stock = Stock::create([
                'product_id' => $productId,
                'store_id' => $storeId,
                'quantity' => $quantity,
                'purchase_price' => $purchasePrice,
                'expiry_date' => $expiryDate,
                'batch_number' => $batchNumber,
            ]);

            $after = $before + $quantity;

            $this->logMovement(
                productId: $productId,
                storeId: $storeId,
                movementType: $this->inferMovementType('in', $referenceType),
                referenceType: $referenceType,
                referenceId: $referenceId,
                quantity: $quantity,
                beforeQuantity: $before,
                afterQuantity: $after,
                note: $note,
                createdBy: $createdBy,
            );

            return $stock;
        });
    }

    public function deductStock(
        int $productId,
        int $storeId,
        float $quantity,
        ?string $referenceType,
        ?int $referenceId,
        int $createdBy,
        ?string $note = null,
    ): void {
        DB::transaction(function () use ($productId, $storeId, $quantity, $referenceType, $referenceId, $createdBy, $note) {
            $before = $this->getAvailableStock($productId, $storeId);

            if ($before < $quantity) {
                throw new RuntimeException('Insufficient stock available for this product at the selected store.');
            }

            $remaining = $quantity;

            $batches = Stock::where('product_id', $productId)
                ->where('store_id', $storeId)
                ->where('quantity', '>', 0)
                ->orderBy('created_at')
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            foreach ($batches as $batch) {
                if ($remaining <= 0) {
                    break;
                }

                $deduct = min($remaining, (float) $batch->quantity);
                $batch->decrement('quantity', $deduct);
                $remaining -= $deduct;
            }

            $after = $before - $quantity;

            $this->logMovement(
                productId: $productId,
                storeId: $storeId,
                movementType: $this->inferMovementType('out', $referenceType),
                referenceType: $referenceType,
                referenceId: $referenceId,
                quantity: $quantity,
                beforeQuantity: $before,
                afterQuantity: $after,
                note: $note,
                createdBy: $createdBy,
            );
        });

        $this->checkLowStock($productId);
    }

    public function checkLowStock(int $productId): void
    {
        $product = Product::find($productId);

        if (! $product || (float) $product->min_stock_threshold <= 0) {
            return;
        }

        $available = $this->getAvailableStock($productId);

        if ($available >= (float) $product->min_stock_threshold) {
            return;
        }

        $recipients = User::where('is_admin', true)
            ->orWhereHas('roles', fn ($q) => $q->where('name', 'Store Manager'))
            ->get();

        Notification::send($recipients, new LowStockNotification($product, $available));
    }

    public function getAvailableStock(int $productId, ?int $storeId = null): float
    {
        $query = Stock::where('product_id', $productId);

        if ($storeId !== null) {
            $query->where('store_id', $storeId);
        }

        return (float) $query->sum('quantity');
    }

    public function logMovement(
        int $productId,
        int $storeId,
        string $movementType,
        ?string $referenceType,
        ?int $referenceId,
        float $quantity,
        float $beforeQuantity,
        float $afterQuantity,
        ?string $note,
        int $createdBy,
    ): StockMovement {
        return StockMovement::create([
            'product_id' => $productId,
            'store_id' => $storeId,
            'movement_type' => $movementType,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'quantity' => $quantity,
            'before_quantity' => $beforeQuantity,
            'after_quantity' => $afterQuantity,
            'note' => $note,
            'created_by' => $createdBy,
        ]);
    }

    private function inferMovementType(string $direction, ?string $referenceType): string
    {
        if ($referenceType === null) {
            return 'adjustment';
        }

        $basename = class_basename($referenceType);

        if ($direction === 'in') {
            return str_contains($basename, 'Return') ? 'return_in' : 'purchase_in';
        }

        return str_contains($basename, 'PurchaseReturn') ? 'purchase_return_out' : 'sale_out';
    }
}
