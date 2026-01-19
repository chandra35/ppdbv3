<?php

namespace App\Mail;

use App\Models\CalonSiswa;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class HasilSeleksiNotification extends Mailable
{
    use Queueable, SerializesModels;

    public CalonSiswa $calonSiswa;
    public string $hasil; // 'diterima' atau 'ditolak'
    public ?string $keterangan;
    public ?object $settings;

    /**
     * Create a new message instance.
     */
    public function __construct(CalonSiswa $calonSiswa, string $hasil, ?string $keterangan = null)
    {
        $this->calonSiswa = $calonSiswa;
        $this->hasil = $hasil;
        $this->keterangan = $keterangan;
        $this->settings = \App\Models\PpdbSettings::first();
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $emoji = $this->hasil === 'diterima' ? '🎉' : '📋';
        $status = $this->hasil === 'diterima' ? 'SELAMAT! Anda Diterima' : 'Pengumuman Hasil Seleksi';
        
        return new Envelope(
            subject: "{$emoji} {$status} - " . ($this->settings->nama_sekolah ?? 'MAN 1 Metro'),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.hasil-seleksi',
            with: [
                'calonSiswa' => $this->calonSiswa,
                'hasil' => $this->hasil,
                'keterangan' => $this->keterangan,
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
