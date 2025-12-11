<?php

namespace App\Services;

use App\Models\PengaturanWa;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    protected ?PengaturanWa $settings;

    public function __construct()
    {
        $this->settings = PengaturanWa::first();
    }

    /**
     * Check if WhatsApp service is active
     */
    public function isActive(): bool
    {
        return $this->settings && $this->settings->is_active && $this->settings->api_key;
    }

    /**
     * Send WhatsApp message
     */
    public function send(string $phone, string $message): array
    {
        if (!$this->isActive()) {
            return [
                'success' => false,
                'message' => 'WhatsApp service is not active',
            ];
        }

        // Format phone number (remove leading 0, add 62)
        $phone = $this->formatPhoneNumber($phone);

        try {
            $result = match ($this->settings->provider) {
                'fonnte' => $this->sendViaFonnte($phone, $message),
                'wablas' => $this->sendViaWablas($phone, $message),
                'wabotapi' => $this->sendViaWabotapi($phone, $message),
                'twilio' => $this->sendViaTwilio($phone, $message),
                default => $this->sendViaGeneric($phone, $message),
            };

            Log::info('WhatsApp message sent', [
                'provider' => $this->settings->provider,
                'phone' => $phone,
                'success' => $result['success'],
            ]);

            return $result;

        } catch (\Exception $e) {
            Log::error('WhatsApp send failed', [
                'provider' => $this->settings->provider,
                'phone' => $phone,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Gagal mengirim pesan: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Send registration credentials
     */
    public function sendRegistrationCredentials(array $data): array
    {
        if (!$this->isActive()) {
            return [
                'success' => false,
                'message' => 'WhatsApp service is not active',
            ];
        }

        $template = $this->settings->template_registrasi ?? PengaturanWa::getDefaultTemplates()['template_registrasi'];
        $message = $this->parseTemplate($template, $data);

        return $this->send($data['phone'], $message);
    }

    /**
     * Send verification notification
     */
    public function sendVerificationNotification(array $data): array
    {
        if (!$this->isActive()) {
            return ['success' => false, 'message' => 'WhatsApp service is not active'];
        }

        $template = $this->settings->template_verifikasi ?? PengaturanWa::getDefaultTemplates()['template_verifikasi'];
        $message = $this->parseTemplate($template, $data);

        return $this->send($data['phone'], $message);
    }

    /**
     * Send acceptance notification
     */
    public function sendAcceptanceNotification(array $data): array
    {
        if (!$this->isActive()) {
            return ['success' => false, 'message' => 'WhatsApp service is not active'];
        }

        $template = $this->settings->template_diterima ?? PengaturanWa::getDefaultTemplates()['template_diterima'];
        $message = $this->parseTemplate($template, $data);

        return $this->send($data['phone'], $message);
    }

    /**
     * Send rejection notification
     */
    public function sendRejectionNotification(array $data): array
    {
        if (!$this->isActive()) {
            return ['success' => false, 'message' => 'WhatsApp service is not active'];
        }

        $template = $this->settings->template_ditolak ?? PengaturanWa::getDefaultTemplates()['template_ditolak'];
        $message = $this->parseTemplate($template, $data);

        return $this->send($data['phone'], $message);
    }

    /**
     * Format phone number to international format (62xxx)
     */
    protected function formatPhoneNumber(string $phone): string
    {
        // Remove all non-numeric characters
        $phone = preg_replace('/[^0-9]/', '', $phone);

        // Remove leading 0 and add 62
        if (str_starts_with($phone, '0')) {
            $phone = '62' . substr($phone, 1);
        }

        // Add 62 if not present
        if (!str_starts_with($phone, '62')) {
            $phone = '62' . $phone;
        }

        return $phone;
    }

    /**
     * Parse template with data
     */
    protected function parseTemplate(string $template, array $data): string
    {
        foreach ($data as $key => $value) {
            $template = str_replace('{' . $key . '}', $value ?? '', $template);
        }
        return $template;
    }

    /**
     * Send via Fonnte
     */
    protected function sendViaFonnte(string $phone, string $message): array
    {
        $response = Http::withHeaders([
            'Authorization' => $this->settings->api_key,
        ])->post($this->settings->api_url, [
            'target' => $phone,
            'message' => $message,
        ]);

        $data = $response->json();

        return [
            'success' => $response->successful() && ($data['status'] ?? false),
            'message' => $data['reason'] ?? $data['message'] ?? 'Unknown response',
            'response' => $data,
        ];
    }

    /**
     * Send via Wablas
     */
    protected function sendViaWablas(string $phone, string $message): array
    {
        $response = Http::withHeaders([
            'Authorization' => $this->settings->api_key,
        ])->post($this->settings->api_url, [
            'phone' => $phone,
            'message' => $message,
        ]);

        $data = $response->json();

        return [
            'success' => $response->successful() && ($data['status'] ?? false),
            'message' => $data['message'] ?? 'Unknown response',
            'response' => $data,
        ];
    }

    /**
     * Send via Wabotapi
     */
    protected function sendViaWabotapi(string $phone, string $message): array
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->settings->api_key,
        ])->post($this->settings->api_url, [
            'phone' => $phone,
            'message' => $message,
        ]);

        $data = $response->json();

        return [
            'success' => $response->successful(),
            'message' => $data['message'] ?? 'Unknown response',
            'response' => $data,
        ];
    }

    /**
     * Send via Twilio
     */
    protected function sendViaTwilio(string $phone, string $message): array
    {
        // Twilio requires account SID and auth token
        // API key format: accountSid:authToken
        $credentials = explode(':', $this->settings->api_key);
        
        if (count($credentials) !== 2) {
            return [
                'success' => false,
                'message' => 'Invalid Twilio credentials format. Use accountSid:authToken',
            ];
        }

        [$accountSid, $authToken] = $credentials;

        $response = Http::withBasicAuth($accountSid, $authToken)
            ->asForm()
            ->post("https://api.twilio.com/2010-04-01/Accounts/{$accountSid}/Messages.json", [
                'From' => 'whatsapp:+' . $this->settings->sender_number,
                'To' => 'whatsapp:+' . $phone,
                'Body' => $message,
            ]);

        $data = $response->json();

        return [
            'success' => $response->successful(),
            'message' => $data['error_message'] ?? $data['status'] ?? 'Unknown response',
            'response' => $data,
        ];
    }

    /**
     * Send via generic API
     */
    protected function sendViaGeneric(string $phone, string $message): array
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->settings->api_key,
            'Content-Type' => 'application/json',
        ])->post($this->settings->api_url, [
            'phone' => $phone,
            'message' => $message,
        ]);

        return [
            'success' => $response->successful(),
            'message' => 'Message sent',
            'response' => $response->json(),
        ];
    }

    /**
     * Test connection to WhatsApp API
     */
    public function testConnection(): array
    {
        if (!$this->settings || !$this->settings->api_key) {
            return [
                'success' => false,
                'message' => 'API Key belum dikonfigurasi',
            ];
        }

        try {
            // Test based on provider
            $result = match ($this->settings->provider) {
                'fonnte' => $this->testFonnte(),
                'wablas' => $this->testWablas(),
                default => ['success' => true, 'message' => 'Provider tidak memiliki endpoint test'],
            };

            return $result;

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Connection test failed: ' . $e->getMessage(),
            ];
        }
    }

    protected function testFonnte(): array
    {
        $response = Http::withHeaders([
            'Authorization' => $this->settings->api_key,
        ])->post('https://api.fonnte.com/validate', [
            'target' => $this->settings->sender_number,
        ]);

        $data = $response->json();

        return [
            'success' => $response->successful() && ($data['status'] ?? false),
            'message' => $data['reason'] ?? 'Connection successful',
            'response' => $data,
        ];
    }

    protected function testWablas(): array
    {
        $response = Http::withHeaders([
            'Authorization' => $this->settings->api_key,
        ])->get('https://pati.wablas.com/api/device/info');

        $data = $response->json();

        return [
            'success' => $response->successful(),
            'message' => $data['message'] ?? 'Connection successful',
            'response' => $data,
        ];
    }
}
