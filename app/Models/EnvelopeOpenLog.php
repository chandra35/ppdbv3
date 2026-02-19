<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EnvelopeOpenLog extends Model
{
    use HasUuids;

    protected $table = 'envelope_open_logs';

    protected $fillable = [
        'calon_siswa_id',
        'user_id',
        'tahun_pelajaran_id',
        'ip_address',
        'user_agent',
        'latitude',
        'longitude',
        'location_name',
        'opened_at',
    ];

    protected $casts = [
        'opened_at' => 'datetime',
    ];

    // ── Relations ──

    public function calonSiswa(): BelongsTo
    {
        return $this->belongsTo(CalonSiswa::class, 'calon_siswa_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function tahunPelajaran(): BelongsTo
    {
        return $this->belongsTo(TahunPelajaran::class, 'tahun_pelajaran_id');
    }

    // ── Helpers ──

    /**
     * Cek apakah pendaftar sudah pernah membuka amplop
     */
    public static function hasOpened($calonSiswaId, $tahunPelajaranId = null): bool
    {
        $query = static::where('calon_siswa_id', $calonSiswaId);
        if ($tahunPelajaranId) {
            $query->where('tahun_pelajaran_id', $tahunPelajaranId);
        }
        return $query->exists();
    }

    /**
     * Get log buka amplop untuk pendaftar tertentu
     */
    public static function getLog($calonSiswaId, $tahunPelajaranId = null)
    {
        $query = static::where('calon_siswa_id', $calonSiswaId);
        if ($tahunPelajaranId) {
            $query->where('tahun_pelajaran_id', $tahunPelajaranId);
        }
        return $query->first();
    }
}
