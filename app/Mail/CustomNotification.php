<?php

namespace App\Mail;

use App\Models\CalonSiswa;
use App\Models\PengaturanEmail;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CustomNotification extends Mailable
{
    use Queueable, SerializesModels;

    public CalonSiswa $calonSiswa;
    public string $customSubject;
    public string $customMessage;
    public ?object $settings;
    public ?object $emailSettings;
    public string $renderedBody;

    /**
     * Create a new message instance.
     */
    public function __construct(CalonSiswa $calonSiswa, string $subject, string $message)
    {
        $this->calonSiswa = $calonSiswa;
        $this->customSubject = $subject;
        $this->customMessage = $message;
        $this->settings = \App\Models\PpdbSettings::first();
        $this->emailSettings = PengaturanEmail::getSettings();
        
        // Render body dari template dengan placeholder
        $this->renderedBody = $this->renderBody();
    }

    /**
     * Render body email dengan placeholder
     */
    protected function renderBody(): string
    {
        // Template untuk email custom
        $template = "
            <p>Yth. <strong>{nama_siswa}</strong>,</p>
            <p>{pesan}</p>
            <hr>
            <p><em>Email ini dikirim dari sistem PPDB {nama_sekolah}.</em></p>
        ";
        
        $placeholders = [
            '{nama_siswa}' => $this->calonSiswa->nama_lengkap,
            '{nama_sekolah}' => $this->settings->nama_sekolah ?? 'MAN 1 Metro',
            '{tahun_pelajaran}' => \App\Models\TahunPelajaran::getAktif()?->nama ?? date('Y'),
            '{nomor_registrasi}' => $this->calonSiswa->nomor_registrasi ?? '-',
            '{jalur_pendaftaran}' => $this->calonSiswa->jalurPendaftaran?->nama ?? '-',
            '{pesan}' => nl2br(e($this->customMessage)),
        ];

        return str_replace(array_keys($placeholders), array_values($placeholders), $template);
    }

    /**
     * Get rendered body for logging
     */
    public function getRenderedBody(): string
    {
        return $this->renderedBody;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        // Replace placeholders in subject
        $subject = str_replace(
            ['{nama_sekolah}', '{nama_siswa}'],
            [$this->settings->nama_sekolah ?? 'MAN 1 Metro', $this->calonSiswa->nama_lengkap],
            $this->customSubject
        );

        return new Envelope(
            subject: $subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.template-wrapper',
            with: [
                'body' => $this->renderedBody,
                'settings' => $this->settings,
                'emailSettings' => $this->emailSettings,
                'title' => $this->customSubject,
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
