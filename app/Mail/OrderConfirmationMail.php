<?php

namespace App\Mail;

use App\Models\Sale;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderConfirmationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Sale $sale
    ) {
        $this->sale->loadMissing([
            'customer',
            'items.product',
            'deliveryAddress.branch',
        ]);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Confirmación de tu pedido '
                . $this->sale->folio
                . ' | Sonríe Corriendo',
        );
    }

    public function content(): Content
    {
        $customerName = $this->sale->customer_name
            ?? $this->sale->customer?->name
            ?? 'Cliente';

        $customerEmail = $this->sale->customer_email
            ?? $this->sale->customer?->email
            ?? '';

        return new Content(
            view: 'emails.orders.confirmation',
            with: [
                'sale' => $this->sale,
                'customerName' => $customerName,
                'customerEmail' => $customerEmail,
                'delivery' => $this->sale->deliveryAddress,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}