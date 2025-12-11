<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Support\Facades\Storage;

class Berita extends Model
{
    use HasUuids;

    protected $table = 'beritas';

    protected $fillable = [
        'judul',
        'slug',
        'deskripsi',
        'konten',
        'gambar',
        'status',
        'tanggal_publikasi',
        'kategori',
        'penulis',
        'views',
        'is_featured',
        'shared_to_facebook',
        'facebook_post_id',
    ];

    protected $casts = [
        'tanggal_publikasi' => 'datetime',
        'is_featured' => 'boolean',
        'shared_to_facebook' => 'boolean',
        'views' => 'integer',
    ];

    protected $attributes = [
        'views' => 0,
        'is_featured' => false,
        'shared_to_facebook' => false,
    ];

    /**
     * Scope published
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published')
                     ->whereNotNull('tanggal_publikasi')
                     ->where('tanggal_publikasi', '<=', now());
    }

    /**
     * Scope active (published and sorted)
     */
    public function scopeActive($query)
    {
        return $query->published()->orderBy('tanggal_publikasi', 'desc');       
    }

    /**
     * Scope featured
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Scope by kategori
     */
    public function scopeKategori($query, $kategori)
    {
        return $query->where('kategori', $kategori);
    }

    /**
     * Get gambar URL
     */
    public function getGambarUrlAttribute(): ?string
    {
        if (!$this->gambar) {
            return null;
        }
        return Storage::disk('public')->url($this->gambar);
    }

    /**
     * Get excerpt from deskripsi
     */
    public function getExcerptAttribute(): string
    {
        return \Str::limit(strip_tags($this->deskripsi), 150);
    }

    /**
     * Get reading time
     */
    public function getReadingTimeAttribute(): int
    {
        $wordCount = str_word_count(strip_tags($this->konten ?? $this->deskripsi));
        return max(1, ceil($wordCount / 200));
    }

    /**
     * Increment view count
     */
    public function incrementViews(): void
    {
        $this->increment('views');
    }

    /**
     * Available kategori
     */
    public static function kategoris(): array
    {
        return [
            'pengumuman' => 'Pengumuman',
            'berita' => 'Berita',
            'agenda' => 'Agenda',
            'artikel' => 'Artikel',
            'lainnya' => 'Lainnya',
        ];
    }
}