<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Support\Facades\Cache;

class SiteSettings extends Model
{
    use HasUuids;

    protected $table = 'site_settings';

    protected $fillable = [
        'nama_sekolah',
        'slogan',
        'alamat',
        'telepon',
        'email',
        'website',
        'logo',
        'favicon',
        'facebook_url',
        'facebook_page_id',
        'facebook_access_token',
        'instagram_url',
        'twitter_url',
        'youtube_url',
        'tiktok_url',
        'whatsapp_number',
        'hero_title',
        'hero_subtitle',
        'hero_image',
        'about_content',
        'about_image',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'primary_color',
        'secondary_color',
        'footer_text',
        'copyright_text',
        'google_maps_embed',
        'latitude',
        'longitude',
    ];

    /**
     * Get the settings instance (singleton pattern)
     */
    public static function instance(): self
    {
        return Cache::remember('site_settings', 3600, function () {
            return self::first() ?? self::create([
                'nama_sekolah' => 'MAN 1 Kota Semarang',
                'slogan' => 'Madrasah Hebat, Bermartabat',
            ]);
        });
    }

    /**
     * Get a setting value
     */
    public static function get(string $key, $default = null)
    {
        return self::instance()->$key ?? $default;
    }

    /**
     * Clear settings cache
     */
    public static function clearCache(): void
    {
        Cache::forget('site_settings');
    }

    /**
     * Get logo URL
     */
    public function getLogoUrlAttribute(): string
    {
        return $this->logo 
            ? asset('storage/' . $this->logo)
            : asset('vendor/adminlte/dist/img/AdminLTELogo.png');
    }

    /**
     * Get favicon URL
     */
    public function getFaviconUrlAttribute(): string
    {
        return $this->favicon 
            ? asset('storage/' . $this->favicon)
            : asset('favicon.ico');
    }

    /**
     * Get hero image URL
     */
    public function getHeroImageUrlAttribute(): ?string
    {
        return $this->hero_image 
            ? asset('storage/' . $this->hero_image)
            : null;
    }

    /**
     * Get about image URL
     */
    public function getAboutImageUrlAttribute(): ?string
    {
        return $this->about_image 
            ? asset('storage/' . $this->about_image)
            : null;
    }
    
    /**
     * Get WhatsApp link
     */
    public function getWhatsappLinkAttribute(): ?string
    {
        if (!$this->whatsapp_number) return null;
        $number = preg_replace('/[^0-9]/', '', $this->whatsapp_number);
        if (substr($number, 0, 1) === '0') {
            $number = '62' . substr($number, 1);
        }
        return "https://wa.me/{$number}";
    }
}
