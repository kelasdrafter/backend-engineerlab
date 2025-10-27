<?php

namespace App\Mail;

use App\Models\User;
use App\Models\PremiumProduct;
use App\Models\PremiumTransaction;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PremiumPurchaseMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $product;
    public $transaction;
    public $downloadUrl;

    /**
     * Create a new message instance.
     */
    public function __construct(User $user, PremiumProduct $product, PremiumTransaction $transaction)
    {
        $this->user = $user;
        $this->product = $product;
        $this->transaction = $transaction;
        
        // Product download URL (if available)
        $this->downloadUrl = $product->file_url;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Pembelian Premium Berhasil - ' . $this->product->name,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'premium-purchase',
        );
    }

    /**
     * Get the attachments for the message.
     */
    public function attachments(): array
    {
        return [];
    }
}