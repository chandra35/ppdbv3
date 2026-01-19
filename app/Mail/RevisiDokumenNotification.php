<?php

namespace App\Mail;

use App\Models\CalonSiswa;
use App\Models\CalonDokumen;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RevisiDokumenNotification extends Mailable
{
    use Queueable, SerializesModels;

    public CalonSiswa $calonSiswa;
    public CalonDokumen $dokumen;
    public string $catatan;
    public ?object $settings;

    /**
     * Create a new message instance.
     */
    public function __construct(CalonSiswa $calonSiswa, CalonDokumen $dokumen, string $catatan)
    {
        $this->calonSiswa = $calonSiswa;
        $this->dokumen = $dokumen;
        $this->catatan = $catatan;
        $this->settings = \App\Models\PpdbSettings::first();
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '⚠️ Permintaan Revisi Dokumen PPDB - ' . ($this->settings->nama_sekolah ?? 'MAN 1 Metro'),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.revisi-dokumen',
            with: [
                'calonSiswa' => $this->calonSiswa,
                'dokumen' => $this->dokumen,
                'catatan' => $this->catatan,
                'settings' => $this->settings,
                'loginUrl' => route('pendaftar.login'),
            ],
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
