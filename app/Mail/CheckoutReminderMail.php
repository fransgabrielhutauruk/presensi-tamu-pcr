<?php

namespace App\Mail;

use App\Models\Kunjungan;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CheckoutReminderMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $kunjungan;
    public $checkoutUrl;

    /**
     * Create a new message instance.
     */
    public function __construct(Kunjungan $kunjungan)
    {
        $this->kunjungan = $kunjungan;
        $this->checkoutUrl = route('tamu.checkout', encid($kunjungan->kunjungan_id));
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Pengingat Checkout Kunjungan ke Politeknik Caltex Riau',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.checkout-reminder',
            with: [
                'kunjungan' => $this->kunjungan,
                'checkoutUrl' => $this->checkoutUrl,
                'namaTamu' => $this->kunjungan->tamu->nama_tamu,
                'jenisKelamin' => $this->kunjungan->tamu->jenis_kelamin_tamu,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
