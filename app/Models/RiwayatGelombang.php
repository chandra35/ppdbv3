<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RiwayatGelombang extends Model
{
    use HasUuids;

    protected $table = 'riwayat_gelombang';

    protected $fillable = [
        'calon_siswa_id',
        'dari_gelombang_id',
        'ke_gelombang_id',
        'jalur_pendaftaran_id',
        'tahun_pelajaran_id',
        'nomor_registrasi_lama',
        'nomor_registrasi_baru',
        'status_kelulusan_sebelumnya',
        'dipindahkan_oleh',
        'catatan',
    ];

    // ── Relations ──

    public function calonSiswa(): BelongsTo
    {
        return $this->belongsTo(CalonSiswa::class, 'calon_siswa_id');
    }

    public function dariGelombang(): BelongsTo
    {
        return $this->belongsTo(GelombangPendaftaran::class, 'dari_gelombang_id');
    }

    public function keGelombang(): BelongsTo
    {
        return $this->belongsTo(GelombangPendaftaran::class, 'ke_gelombang_id');
    }

    public function jalurPendaftaran(): BelongsTo
    {
        return $this->belongsTo(JalurPendaftaran::class, 'jalur_pendaftaran_id');
    }

    public function tahunPelajaran(): BelongsTo
    {
        return $this->belongsTo(TahunPelajaran::class, 'tahun_pelajaran_id');
    }

    // ── Helpers ──

    /**
     * Get label perpindahan (misal "Gelombang 1 → Gelombang 2")
     */
    public function getLabelAttribute(): string
    {
        $dari = $this->dariGelombang->nama ?? '?';
        $ke = $this->keGelombang->nama ?? '?';
        return "{$dari} → {$ke}";
    }

    /**
     * Cek berapa kali pendaftar sudah pindah gelombang
     */
    public static function countPindah($calonSiswaId, $tahunPelajaranId = null): int
    {
        $query = static::where('calon_siswa_id', $calonSiswaId);
        if ($tahunPelajaranId) {
            $query->where('tahun_pelajaran_id', $tahunPelajaranId);
        }
        return $query->count();
    }

    /**
     * Get riwayat perpindahan gelombang untuk pendaftar
     */
    public static function getRiwayat($calonSiswaId, $tahunPelajaranId = null)
    {
        $query = static::with(['dariGelombang', 'keGelombang'])
            ->where('calon_siswa_id', $calonSiswaId)
            ->orderBy('created_at', 'asc');
        if ($tahunPelajaranId) {
            $query->where('tahun_pelajaran_id', $tahunPelajaranId);
        }
        return $query->get();
    }
}
