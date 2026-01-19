<?php

namespace App\Mail;

use App\Models\CalonSiswa;
use App\Models\PengaturanEmail;
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
    public ?object $emailSettings;
    public string $renderedBody;

    /**
     * Create a new message instance.
     */
    public function __construct(CalonSiswa $calonSiswa, string $nomorTes)
    {
        $this->calonSiswa = $calonSiswa;
        $this->nomorTes = $nomorTes;
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
        $template = $this->emailSettings?->template_nomor_tes 
            ?? PengaturanEmail::getDefaultTemplates()['template_nomor_tes'];
        
        $placeholders = [
            '{nama_siswa}' => $this->calonSiswa->nama_lengkap,
            '{nama_sekolah}' => $this->settings->nama_sekolah ?? 'MAN 1 Metro',
            '{tahun_pelajaran}' => \App\Models\TahunPelajaran::getActive()?->nama ?? date('Y'),
            '{nomor_registrasi}' => $this->calonSiswa->nomor_registrasi ?? '-',
            '{nomor_tes}' => $this->nomorTes,
            '{jalur_pendaftaran}' => $this->calonSiswa->jalurPendaftaran?->nama ?? '-',
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
        $subject = $this->emailSettings?->subject_nomor_tes 
            ?? PengaturanEmail::getDefaultTemplates()['subject_nomor_tes'];
        
        // Replace placeholders in subject
        $subject = str_replace(
            ['{nama_sekolah}', '{nama_siswa}'],
            [$this->settings->nama_sekolah ?? 'MAN 1 Metro', $this->calonSiswa->nama_lengkap],
            $subject
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
                'title' => 'Nomor Tes PPDB',
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
