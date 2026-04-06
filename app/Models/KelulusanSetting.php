<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class KelulusanSetting extends Model
{
    use HasUuids;

    protected $table = 'kelulusan_settings';

    protected $fillable = [
        'tahun_pelajaran_id',
        'jalur_pendaftaran_id',
        'gelombang_pendaftaran_id',
        'judul_pengumuman',
        'pesan_lulus',
        'pesan_tidak_lulus',
        'link_grup_wa',
        'nama_grup_wa',
        'dokumen_persyaratan',
        'template_surat_pernyataan',
        'file_konsider',
        'tampilkan_pengumuman',
        'tanggal_pengumuman',
        'tampilkan_link_wa',
        'tampilkan_dokumen',
        'tanggal_daftar_ulang_mulai',
        'tanggal_daftar_ulang_selesai',
        'catatan_daftar_ulang',
    ];

    protected $casts = [
        'dokumen_persyaratan' => 'array',
        'tampilkan_pengumuman' => 'boolean',
        'tampilkan_link_wa' => 'boolean',
        'tampilkan_dokumen' => 'boolean',
        'tanggal_pengumuman' => 'datetime',
        'tanggal_daftar_ulang_mulai' => 'date',
        'tanggal_daftar_ulang_selesai' => 'date',
    ];

    /**
     * Cek apakah pengumuman sudah aktif berdasarkan toggle + tanggal/jam
     * - tampilkan_pengumuman harus true
     * - Jika tanggal_pengumuman diisi, waktu sekarang harus >= tanggal_pengumuman
     * - Jika tanggal_pengumuman kosong, langsung aktif saat toggle ON
     */
    public function isPengumumanAktif(): bool
    {
        if (!$this->tampilkan_pengumuman) {
            return false;
        }

        if ($this->tanggal_pengumuman && now()->lt($this->tanggal_pengumuman)) {
            return false;
        }

        return true;
    }

    // Relations
    public function tahunPelajaran(): BelongsTo
    {
        return $this->belongsTo(TahunPelajaran::class, 'tahun_pelajaran_id');
    }

    public function jalurPendaftaran(): BelongsTo
    {
        return $this->belongsTo(JalurPendaftaran::class, 'jalur_pendaftaran_id');
    }

    public function gelombangPendaftaran(): BelongsTo
    {
        return $this->belongsTo(GelombangPendaftaran::class, 'gelombang_pendaftaran_id');
    }

    public function scopeForContext(
        Builder $query,
        ?string $tahunPelajaranId,
        ?string $jalurPendaftaranId = null,
        ?string $gelombangPendaftaranId = null
    ): Builder {
        return $query
            ->where('tahun_pelajaran_id', $tahunPelajaranId)
            ->where('jalur_pendaftaran_id', $jalurPendaftaranId)
            ->where('gelombang_pendaftaran_id', $gelombangPendaftaranId);
    }

    public function getScopeLabelAttribute(): string
    {
        if ($this->gelombangPendaftaran) {
            return 'Gelombang';
        }

        if ($this->jalurPendaftaran) {
            return 'Jalur';
        }

        return 'Tahun Pelajaran';
    }

    public function getScopeDescriptionAttribute(): string
    {
        $parts = [];

        if ($this->tahunPelajaran) {
            $parts[] = 'Tahun ' . $this->tahunPelajaran->nama;
        }

        if ($this->jalurPendaftaran) {
            $parts[] = 'Jalur ' . $this->jalurPendaftaran->nama;
        }

        if ($this->gelombangPendaftaran) {
            $parts[] = 'Gelombang ' . $this->gelombangPendaftaran->nama;
        }

        return implode(' • ', $parts);
    }

    public static function defaultAttributes(): array
    {
        return [
            'judul_pengumuman' => 'Pengumuman Kelulusan PPDB',
            'pesan_lulus' => 'Selamat! Anda dinyatakan LULUS seleksi PPDB. Silakan bergabung ke grup WhatsApp dan lengkapi persyaratan daftar ulang.',
            'pesan_tidak_lulus' => 'Mohon maaf, Anda belum dinyatakan lulus pada seleksi PPDB tahun ini. Tetap semangat dan jangan menyerah!',
        ];
    }

    public static function resolveFor(
        ?string $tahunPelajaranId,
        ?string $jalurPendaftaranId = null,
        ?string $gelombangPendaftaranId = null
    ): ?self {
        if (!$tahunPelajaranId) {
            return null;
        }

        $fallbacks = [];

        if ($gelombangPendaftaranId) {
            $fallbacks[] = [$tahunPelajaranId, $jalurPendaftaranId, $gelombangPendaftaranId];
        }

        if ($jalurPendaftaranId) {
            $fallbacks[] = [$tahunPelajaranId, $jalurPendaftaranId, null];
        }

        $fallbacks[] = [$tahunPelajaranId, null, null];

        foreach ($fallbacks as [$tahunId, $jalurId, $gelombangId]) {
            $setting = static::forContext($tahunId, $jalurId, $gelombangId)->first();
            if ($setting) {
                return $setting;
            }
        }

        return null;
    }

    public static function getOrCreateForContext(
        ?string $tahunPelajaranId,
        ?string $jalurPendaftaranId = null,
        ?string $gelombangPendaftaranId = null
    ): ?self {
        if (!$tahunPelajaranId) {
            return null;
        }

        return static::firstOrCreate(
            [
                'tahun_pelajaran_id' => $tahunPelajaranId,
                'jalur_pendaftaran_id' => $jalurPendaftaranId,
                'gelombang_pendaftaran_id' => $gelombangPendaftaranId,
            ],
            static::defaultAttributes()
        );
    }

    /**
     * Get setting for active tahun pelajaran
     */
    public static function getActive(?CalonSiswa $calonSiswa = null)
    {
        if ($calonSiswa) {
            return static::resolveFor(
                $calonSiswa->tahun_pelajaran_id,
                $calonSiswa->jalur_pendaftaran_id,
                $calonSiswa->gelombang_pendaftaran_id
            ) ?? static::getOrCreateForContext($calonSiswa->tahun_pelajaran_id, null, null);
        }

        $tahunAktif = TahunPelajaran::where('is_active', true)->first();
        if (!$tahunAktif) {
            return null;
        }

        return static::getOrCreateForContext($tahunAktif->id, null, null);
    }
}
