<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\Purchase;
use App\Models\Sale;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function sale(Request $request, Sale $sale)
    {
        $sale->load(['items.product', 'customer']);

        $invoice = $this->recordInvoice($request, 'sale', Sale::class, $sale->id);

        return $this->respond($request, 'admin.invoices.sale', [
            'sale' => $sale,
            'invoice' => $invoice,
        ], "sale-invoice-{$sale->sale_id}.pdf");
    }

    public function purchase(Request $request, Purchase $purchase)
    {
        $purchase->load(['items.product', 'supplier']);

        $invoice = $this->recordInvoice($request, 'purchase', Purchase::class, $purchase->id);

        return $this->respond($request, 'admin.invoices.purchase', [
            'purchase' => $purchase,
            'invoice' => $invoice,
        ], "purchase-invoice-{$purchase->purchase_id}.pdf");
    }

    public function expense(Request $request, Expense $expense)
    {
        $expense->load(['expenseHead']);

        $invoice = $this->recordInvoice($request, 'expense', Expense::class, $expense->id);

        return $this->respond($request, 'admin.invoices.expense', [
            'expense' => $expense,
            'invoice' => $invoice,
        ], "expense-invoice-{$expense->expense_id}.pdf");
    }

    public function order(Request $request, Order $order)
    {
        $order->load(['items.product', 'client']);

        $invoice = $this->recordInvoice($request, 'order', Order::class, $order->id);

        return $this->respond($request, 'admin.invoices.order', [
            'order' => $order,
            'invoice' => $invoice,
        ], "order-invoice-{$order->order_id}.pdf");
    }

    private function recordInvoice(Request $request, string $type, string $referenceClass, int $referenceId): Invoice
    {
        return Invoice::firstOrCreate(
            [
                'invoice_type' => $type,
                'reference_type' => $referenceClass,
                'reference_id' => $referenceId,
            ],
            [
                'generated_by' => $request->user()->id,
                'generated_at' => now(),
            ]
        );
    }

    private function respond(Request $request, string $view, array $data, string $filename)
    {
        if ($request->query('format') === 'pdf') {
            return Pdf::loadView($view, $data)->download($filename);
        }

        return view($view, $data);
    }
}
