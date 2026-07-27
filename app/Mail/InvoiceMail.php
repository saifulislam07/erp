<?php

namespace App\Mail;

use App\Models\Invoice;
use App\Models\Purchase;
use App\Models\Sale;
use App\Support\Branding;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Emails a sale or purchase invoice to the other party with the PDF attached.
 *
 * Queued: rendering a PDF and talking to an SMTP server both take long enough
 * that doing it inside the request would make the button feel broken.
 */
class InvoiceMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Sale|Purchase $document,
        public readonly Invoice $invoice,
        public readonly ?string $message = null,
    ) {}

    public function envelope(): Envelope
    {
        $label = $this->isSale() ? 'Invoice' : 'Purchase order';

        return new Envelope(
            subject: sprintf('%s %s from %s', $label, $this->reference(), Branding::name()),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.invoice',
            with: [
                'isSale' => $this->isSale(),
                'reference' => $this->reference(),
                'partyName' => $this->partyName(),
                'total' => (float) $this->document->total_amount,
                'due' => (float) $this->document->due_amount,
                'dated' => $this->isSale() ? $this->document->sale_date : $this->document->purchase_date,
                'note' => $this->message,
                'company' => Branding::name(),
            ],
        );
    }

    /**
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        $view = $this->isSale() ? 'admin.invoices.sale' : 'admin.invoices.purchase';
        $key = $this->isSale() ? 'sale' : 'purchase';

        // `pdf => true` tells the shared invoice layout to drop the toolbar and
        // the remote logo, neither of which DomPDF can render.
        $pdf = Pdf::loadView($view, [
            $key => $this->document,
            'invoice' => $this->invoice,
            'pdf' => true,
        ]);

        return [
            Attachment::fromData(fn () => $pdf->output(), $this->reference().'.pdf')
                ->withMime('application/pdf'),
        ];
    }

    private function isSale(): bool
    {
        return $this->document instanceof Sale;
    }

    private function reference(): string
    {
        return $this->isSale() ? $this->document->sale_id : $this->document->purchase_id;
    }

    private function partyName(): string
    {
        if ($this->isSale()) {
            return $this->document->customer?->name ?: ($this->document->customer_name ?: 'Customer');
        }

        return $this->document->supplier?->name ?: 'Supplier';
    }
}
