<?php

namespace App\Mail;

use App\Models\CalonSiswa;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NomorTesNotification extends Mailable
{
    use Queueable, SerializesModels;

    public CalonSiswa $calonSiswa;
    public string $nomorTes;
    public ?object $settings;

    /**
     * Create a new message instance.
     */
    public function __construct(CalonSiswa $calonSiswa, string $nomorTes)
    {
        $this->calonSiswa = $calonSiswa;
        $this->nomorTes = $nomorTes;
        $this->settings = \App\Models\PpdbSettings::first();
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '🎉 Nomor Tes PPDB - ' . ($this->settings->nama_sekolah ?? 'MAN 1 Metro'),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.nomor-tes',
            with: [
                'calonSiswa' => $this->calonSiswa,
                'nomorTes' => $this->nomorTes,
                'settings' => $this->settings,
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
