<?php

namespace App\Mail;

use App\Models\CalonSiswa;
use App\Models\PengaturanEmail;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PasswordResetNotification extends Mailable
{
    use Queueable, SerializesModels;

    public CalonSiswa $calonSiswa;
    public string $resetLink;
    public ?object $settings;
    public ?object $emailSettings;
    public string $renderedBody;

    /**
     * Create a new message instance.
     */
    public function __construct(CalonSiswa $calonSiswa, string $resetLink)
    {
        $this->calonSiswa = $calonSiswa;
        $this->resetLink = $resetLink;
        $this->settings = \App\Models\PpdbSettings::first();
        $this->emailSettings = PengaturanEmail::getSettings();
        
        $this->renderedBody = $this->renderBody();
    }

    /**
     * Render body email
     */
    protected function renderBody(): string
    {
        $namaSekolah = $this->settings->nama_sekolah ?? 'MAN 1 Metro';

        return "
            <p>Yth. <strong>{$this->calonSiswa->nama_lengkap}</strong>,</p>
            <p>Kami menerima permintaan untuk mereset password akun PPDB Anda.</p>
            <p>Klik tombol di bawah ini untuk mengatur password baru:</p>
            <center>
                <a href=\"{$this->resetLink}\" class=\"btn\" style=\"display:inline-block;background:linear-gradient(135deg,#f59e0b,#d97706);color:white!important;text-decoration:none;padding:14px 40px;border-radius:25px;font-weight:bold;font-size:16px;margin:20px 0;\">
                    🔑 Reset Password
                </a>
            </center>
            <p style=\"color: #666; font-size: 0.9rem;\">
                Atau salin link berikut ke browser Anda:<br>
                <a href=\"{$this->resetLink}\" style=\"color:#007bff;word-break:break-all;\">{$this->resetLink}</a>
            </p>
            <div style=\"background:#fff3cd;border:1px solid #ffc107;border-radius:8px;padding:12px 16px;margin:15px 0;\">
                <strong>⚠️ Penting:</strong>
                <ul style=\"margin:8px 0 0 0;padding-left:20px;\">
                    <li>Link ini berlaku selama <strong>60 menit</strong>.</li>
                    <li>Jika Anda tidak merasa meminta reset password, abaikan email ini.</li>
                    <li>Jangan bagikan link ini kepada siapapun.</li>
                </ul>
            </div>
            <hr style=\"border:none;border-top:1px solid #eee;margin:20px 0;\">
            <p style=\"font-size:0.85rem;color:#999;\">
                <em>Email ini dikirim otomatis dari sistem PPDB {$namaSekolah}. Mohon tidak membalas email ini.</em>
            </p>
        ";
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
        $namaSekolah = $this->settings->nama_sekolah ?? 'MAN 1 Metro';

        return new Envelope(
            subject: "Reset Password PPDB - {$namaSekolah}",
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
                'title' => 'Reset Password',
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
