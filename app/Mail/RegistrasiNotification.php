<?php

namespace App\Mail;

use App\Models\CalonSiswa;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RegistrasiNotification extends Mailable
{
    use Queueable, SerializesModels;

    public CalonSiswa $calonSiswa;
    public string $username;
    public string $password;
    public ?object $settings;

    /**
     * Create a new message instance.
     */
    public function __construct(CalonSiswa $calonSiswa, string $username, string $password)
    {
        $this->calonSiswa = $calonSiswa;
        $this->username = $username;
        $this->password = $password;
        $this->settings = \App\Models\PpdbSettings::first();
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '✅ Pendaftaran PPDB Berhasil - ' . ($this->settings->nama_sekolah ?? 'MAN 1 Metro'),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.registrasi',
            with: [
                'calonSiswa' => $this->calonSiswa,
                'username' => $this->username,
                'password' => $this->password,
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
