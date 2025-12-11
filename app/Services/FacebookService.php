<?php

namespace App\Services;

use App\Models\Berita;
use App\Models\SiteSettings;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FacebookService
{
    protected ?string $pageId;
    protected ?string $accessToken;
    protected string $graphApiVersion = 'v18.0';
    protected string $baseUrl = 'https://graph.facebook.com';

    public function __construct()
    {
        $settings = SiteSettings::instance();
        $this->pageId = $settings->facebook_page_id;
        $this->accessToken = $settings->facebook_access_token;
    }

    /**
     * Check if Facebook is configured
     */
    public function isConfigured(): bool
    {
        return !empty($this->pageId) && !empty($this->accessToken);
    }

    /**
     * Share berita to Facebook page
     */
    public function shareBerita(Berita $berita): array
    {
        if (!$this->isConfigured()) {
            return [
                'success' => false,
                'message' => 'Facebook belum dikonfigurasi. Silakan atur Page ID dan Access Token di pengaturan.',
            ];
        }

        try {
            $message = $this->formatBeritaMessage($berita);
            $link = route('ppdb.berita.show', $berita->slug);

            // If berita has image, share as photo post
            if ($berita->gambar) {
                return $this->shareWithPhoto($message, $berita->gambar_url, $link);
            }

            // Share as link post
            return $this->shareLink($message, $link);
        } catch (\Exception $e) {
            Log::error('Facebook sharing failed: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Gagal share ke Facebook: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Share link post
     */
    protected function shareLink(string $message, string $link): array
    {
        $response = Http::post("{$this->baseUrl}/{$this->graphApiVersion}/{$this->pageId}/feed", [
            'message' => $message,
            'link' => $link,
            'access_token' => $this->accessToken,
        ]);

        if ($response->successful()) {
            $data = $response->json();
            return [
                'success' => true,
                'message' => 'Berhasil dishare ke Facebook!',
                'post_id' => $data['id'] ?? null,
            ];
        }

        $error = $response->json();
        return [
            'success' => false,
            'message' => $error['error']['message'] ?? 'Gagal share ke Facebook',
        ];
    }

    /**
     * Share with photo
     */
    protected function shareWithPhoto(string $message, string $imageUrl, string $link): array
    {
        $response = Http::post("{$this->baseUrl}/{$this->graphApiVersion}/{$this->pageId}/photos", [
            'message' => $message . "\n\nBaca selengkapnya: " . $link,
            'url' => $imageUrl,
            'access_token' => $this->accessToken,
        ]);

        if ($response->successful()) {
            $data = $response->json();
            return [
                'success' => true,
                'message' => 'Berhasil dishare ke Facebook dengan foto!',
                'post_id' => $data['post_id'] ?? $data['id'] ?? null,
            ];
        }

        // Fallback to link share if photo fails
        return $this->shareLink($message, $link);
    }

    /**
     * Format berita message for Facebook
     */
    protected function formatBeritaMessage(Berita $berita): string
    {
        $emoji = $this->getKategoriEmoji($berita->kategori);
        
        $message = "{$emoji} {$berita->judul}\n\n";
        $message .= strip_tags($berita->deskripsi);
        
        // Add hashtags
        $hashtags = ['#PPDB', '#Pendaftaran', '#SekolahMenengah'];
        if ($berita->kategori) {
            $hashtags[] = '#' . ucfirst($berita->kategori);
        }
        
        $message .= "\n\n" . implode(' ', $hashtags);
        
        return $message;
    }

    /**
     * Get emoji for kategori
     */
    protected function getKategoriEmoji(?string $kategori): string
    {
        return match ($kategori) {
            'pengumuman' => '📢',
            'berita' => '📰',
            'agenda' => '📅',
            'artikel' => '📝',
            default => '📌',
        };
    }

    /**
     * Delete Facebook post
     */
    public function deletePost(string $postId): array
    {
        if (!$this->isConfigured()) {
            return ['success' => false, 'message' => 'Facebook belum dikonfigurasi'];
        }

        try {
            $response = Http::delete("{$this->baseUrl}/{$this->graphApiVersion}/{$postId}", [
                'access_token' => $this->accessToken,
            ]);

            if ($response->successful()) {
                return ['success' => true, 'message' => 'Post berhasil dihapus dari Facebook'];
            }

            $error = $response->json();
            return [
                'success' => false,
                'message' => $error['error']['message'] ?? 'Gagal menghapus post',
            ];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Verify token validity
     */
    public function verifyToken(): array
    {
        if (!$this->isConfigured()) {
            return ['valid' => false, 'message' => 'Belum dikonfigurasi'];
        }

        try {
            $response = Http::get("{$this->baseUrl}/{$this->graphApiVersion}/me", [
                'access_token' => $this->accessToken,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'valid' => true,
                    'message' => 'Token valid',
                    'name' => $data['name'] ?? null,
                    'id' => $data['id'] ?? null,
                ];
            }

            return ['valid' => false, 'message' => 'Token tidak valid'];
        } catch (\Exception $e) {
            return ['valid' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Get page info
     */
    public function getPageInfo(): ?array
    {
        if (!$this->isConfigured()) {
            return null;
        }

        try {
            $response = Http::get("{$this->baseUrl}/{$this->graphApiVersion}/{$this->pageId}", [
                'fields' => 'id,name,picture,fan_count,link',
                'access_token' => $this->accessToken,
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            return null;
        } catch (\Exception $e) {
            return null;
        }
    }
}
