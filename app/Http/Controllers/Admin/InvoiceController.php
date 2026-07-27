<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\InvoiceMail;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\Purchase;
use App\Models\Sale;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Throwable;

class InvoiceController extends Controller
{
    public function sale(Request $request, Sale $sale)
    {
        $sale->load(['items.product.unit', 'customer', 'creator']);

        $invoice = $this->recordInvoice($request, 'sale', Sale::class, $sale->id);

        return $this->respond($request, 'admin.invoices.sale', [
            'sale' => $sale,
            'invoice' => $invoice,
        ], "sale-invoice-{$sale->sale_id}.pdf");
    }

    public function purchase(Request $request, Purchase $purchase)
    {
        $purchase->load(['items.product.unit', 'supplier', 'creator', 'returns']);

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

    /**
     * Email a sale invoice to the customer, or a purchase order to the supplier.
     */
    public function send(Request $request, string $type, int $id): RedirectResponse
    {
        abort_unless(in_array($type, ['sale', 'purchase'], true), 404);

        $document = $type === 'sale'
            ? Sale::with(['items.product.unit', 'customer', 'creator'])->findOrFail($id)
            : Purchase::with(['items.product.unit', 'supplier', 'creator', 'returns'])->findOrFail($id);

        $recipient = $type === 'sale'
            ? $document->customer?->email
            : $document->supplier?->email;

        if (! $recipient) {
            return back()->with('error', sprintf(
                'No email address is on file for this %s, so the invoice could not be sent.',
                $type === 'sale' ? 'customer' : 'supplier',
            ));
        }

        $invoice = $this->recordInvoice(
            $request,
            $type,
            $type === 'sale' ? Sale::class : Purchase::class,
            $document->id,
        );

        try {
            Mail::to($recipient)->queue(new InvoiceMail($document, $invoice, $request->input('message')));
        } catch (Throwable $exception) {
            return back()->with('error', 'The invoice could not be queued: '.$exception->getMessage());
        }

        return back()->with('success', "Invoice queued for delivery to {$recipient}.");
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
            // The shared layout drops the toolbar and the remote logo in PDF
            // mode — DomPDF can render neither.
            return Pdf::loadView($view, $data + ['pdf' => true])->download($filename);
        }

        return view($view, $data);
    }
}
