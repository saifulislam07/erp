<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PurchaseReturnRequest;
use App\Models\Purchase;
use App\Models\PurchaseReturn;
use App\Services\StockService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PurchaseReturnController extends Controller
{
    public function __construct(private readonly StockService $stockService) {}

    public function index(Purchase $purchase): View
    {
        $returns = $purchase->returns()->with('items.product')->latest()->get();

        return view('admin.purchases.returns.index', compact('purchase', 'returns'));
    }

    public function create(Purchase $purchase): View
    {
        $purchase->load('items.product');

        $rows = $purchase->items->map(function ($item) {
            $returned = $item->purchaseReturnItems()->sum('quantity');

            return (object) [
                'purchase_item_id' => $item->id,
                'product_name' => $item->product->name,
                'purchased_qty' => $item->quantity,
                'returned_qty' => $returned,
                'remaining_qty' => $item->quantity - $returned,
                'unit_price' => $item->purchase_price,
            ];
        })->filter(fn ($row) => $row->remaining_qty > 0)->values();

        return view('admin.purchases.returns.create', compact('purchase', 'rows'));
    }

    public function store(PurchaseReturnRequest $request, Purchase $purchase): RedirectResponse
    {
        DB::transaction(function () use ($request, $purchase) {
            $totalAmount = 0;
            $itemsData = [];

            foreach ($request->items as $row) {
                $quantity = (float) $row['quantity'];

                if ($quantity <= 0) {
                    continue;
                }

                $purchaseItem = $purchase->items()->findOrFail($row['purchase_item_id']);
                $lineTotal = $quantity * (float) $purchaseItem->purchase_price;

                $totalAmount += $lineTotal;

                $itemsData[] = [
                    'purchase_item_id' => $purchaseItem->id,
                    'product_id' => $purchaseItem->product_id,
                    'store_id' => $purchaseItem->store_id,
                    'quantity' => $quantity,
                    'unit_price' => $purchaseItem->purchase_price,
                    'total_price' => $lineTotal,
                ];
            }

            $return = PurchaseReturn::create([
                'purchase_id' => $purchase->id,
                'return_date' => $request->return_date,
                'reason' => $request->reason,
                'total_amount' => $totalAmount,
                'created_by' => $request->user()->id,
            ]);

            foreach ($itemsData as $item) {
                $return->items()->create([
                    'purchase_item_id' => $item['purchase_item_id'],
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total_price' => $item['total_price'],
                ]);

                $this->stockService->deductStock(
                    productId: $item['product_id'],
                    storeId: $item['store_id'],
                    quantity: $item['quantity'],
                    referenceType: PurchaseReturn::class,
                    referenceId: $return->id,
                    createdBy: $request->user()->id,
                    note: "Purchase return {$return->return_id}",
                );
            }
        });

        return redirect()->route('admin.purchases.returns.index', $purchase)->with('success', 'Purchase return recorded successfully.');
    }

    public function edit(Purchase $purchase, PurchaseReturn $return): View
    {
        $return->load('items');
        $purchase->load('items.product');

        $rows = $purchase->items->map(function ($item) use ($return) {
            $returnedElsewhere = $item->purchaseReturnItems()
                ->where('purchase_return_id', '!=', $return->id)
                ->sum('quantity');

            $currentReturnItem = $return->items->firstWhere('purchase_item_id', $item->id);

            return (object) [
                'purchase_item_id' => $item->id,
                'product_name' => $item->product->name,
                'purchased_qty' => $item->quantity,
                'remaining_qty' => $item->quantity - $returnedElsewhere,
                'unit_price' => $item->purchase_price,
                'current_quantity' => $currentReturnItem->quantity ?? 0,
            ];
        })->values();

        return view('admin.purchases.returns.edit', compact('purchase', 'return', 'rows'));
    }

    public function update(PurchaseReturnRequest $request, Purchase $purchase, PurchaseReturn $return): RedirectResponse
    {
        DB::transaction(function () use ($request, $purchase, $return) {
            foreach ($return->items as $oldItem) {
                $purchaseItem = $purchase->items()->find($oldItem->purchase_item_id);

                $this->stockService->addStock(
                    productId: $oldItem->product_id,
                    storeId: $purchaseItem->store_id,
                    quantity: (float) $oldItem->quantity,
                    purchasePrice: $purchaseItem->purchase_price,
                    expiryDate: $purchaseItem->expiry_date?->format('Y-m-d'),
                    referenceType: PurchaseReturn::class,
                    referenceId: $return->id,
                    createdBy: $request->user()->id,
                    note: "Reversal for editing return {$return->return_id}",
                );
            }

            $return->items()->delete();

            $totalAmount = 0;

            foreach ($request->items as $row) {
                $quantity = (float) $row['quantity'];

                if ($quantity <= 0) {
                    continue;
                }

                $purchaseItem = $purchase->items()->findOrFail($row['purchase_item_id']);
                $lineTotal = $quantity * (float) $purchaseItem->purchase_price;
                $totalAmount += $lineTotal;

                $return->items()->create([
                    'purchase_item_id' => $purchaseItem->id,
                    'product_id' => $purchaseItem->product_id,
                    'quantity' => $quantity,
                    'unit_price' => $purchaseItem->purchase_price,
                    'total_price' => $lineTotal,
                ]);

                $this->stockService->deductStock(
                    productId: $purchaseItem->product_id,
                    storeId: $purchaseItem->store_id,
                    quantity: $quantity,
                    referenceType: PurchaseReturn::class,
                    referenceId: $return->id,
                    createdBy: $request->user()->id,
                    note: "Updated purchase return {$return->return_id}",
                );
            }

            $return->update([
                'return_date' => $request->return_date,
                'reason' => $request->reason,
                'total_amount' => $totalAmount,
            ]);
        });

        return redirect()->route('admin.purchases.returns.index', $purchase)->with('success', 'Purchase return updated successfully.');
    }

    public function destroy(Purchase $purchase, PurchaseReturn $return): RedirectResponse
    {
        DB::transaction(function () use ($purchase, $return) {
            foreach ($return->items as $item) {
                $purchaseItem = $purchase->items()->find($item->purchase_item_id);

                $this->stockService->addStock(
                    productId: $item->product_id,
                    storeId: $purchaseItem->store_id,
                    quantity: (float) $item->quantity,
                    purchasePrice: $purchaseItem->purchase_price,
                    expiryDate: $purchaseItem->expiry_date?->format('Y-m-d'),
                    referenceType: PurchaseReturn::class,
                    referenceId: $return->id,
                    createdBy: request()->user()->id,
                    note: "Reversal for deleted return {$return->return_id}",
                );
            }

            $return->items()->delete();
            $return->delete();
        });

        return redirect()->route('admin.purchases.returns.index', $purchase)->with('success', 'Purchase return deleted successfully.');
    }
}
