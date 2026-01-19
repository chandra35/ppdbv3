<?php

namespace App\Mail;

use App\Models\CalonSiswa;
use App\Models\PengaturanEmail;
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
    public ?object $emailSettings;
    public string $renderedBody;

    /**
     * Create a new message instance.
     */
    public function __construct(CalonSiswa $calonSiswa, string $hasil, ?string $keterangan = null)
    {
        $this->calonSiswa = $calonSiswa;
        $this->hasil = $hasil;
        $this->keterangan = $keterangan;
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
        $templateKey = $this->hasil === 'diterima' ? 'template_diterima' : 'template_ditolak';
        $template = $this->emailSettings?->{$templateKey} 
            ?? PengaturanEmail::getDefaultTemplates()[$templateKey];
        
        $placeholders = [
            '{nama_siswa}' => $this->calonSiswa->nama_lengkap,
            '{nama_sekolah}' => $this->settings->nama_sekolah ?? 'MAN 1 Metro',
            '{tahun_pelajaran}' => \App\Models\TahunPelajaran::getAktif()?->nama ?? date('Y'),
            '{nomor_registrasi}' => $this->calonSiswa->nomor_registrasi ?? '-',
            '{jalur_pendaftaran}' => $this->calonSiswa->jalurPendaftaran?->nama ?? '-',
            '{catatan}' => $this->keterangan ?? '',
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
        $subjectKey = $this->hasil === 'diterima' ? 'subject_diterima' : 'subject_ditolak';
        $subject = $this->emailSettings?->{$subjectKey} 
            ?? PengaturanEmail::getDefaultTemplates()[$subjectKey];
        
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
        $title = $this->hasil === 'diterima' ? '🎉 Selamat! Anda Diterima' : 'Pengumuman Hasil Seleksi';
        
        return new Content(
            view: 'emails.template-wrapper',
            with: [
                'body' => $this->renderedBody,
                'settings' => $this->settings,
                'emailSettings' => $this->emailSettings,
                'title' => $title,
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
