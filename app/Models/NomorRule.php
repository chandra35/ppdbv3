<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class NomorRule extends Model
{
    use HasUuids;

    protected $fillable = [
        'nama_rule',
        'jenis_nomor',
        'scope_type',
        'scope_id',
        'prefix',
        'format',
        'digit',
        'nomor_awal',
        'nomor_akhir',
        'mode_counter',
        'source_rule_id',
        'is_active',
        'keterangan',
    ];

    protected $casts = [
        'digit' => 'integer',
        'nomor_awal' => 'integer',
        'nomor_akhir' => 'integer',
        'is_active' => 'boolean',
    ];

    public const JENIS_REGISTRASI = 'registrasi';
    public const JENIS_TES = 'tes';

    public const SCOPE_GLOBAL = 'global';
    public const SCOPE_TAHUN = 'tahun';
    public const SCOPE_JALUR = 'jalur';
    public const SCOPE_GELOMBANG = 'gelombang';

    public const MODE_RESET = 'reset';
    public const MODE_MANUAL = 'manual';
    public const MODE_LANJUT = 'lanjut_rule_lain';

    public function sourceRule(): BelongsTo
    {
        return $this->belongsTo(self::class, 'source_rule_id');
    }

    public function sequence(): HasOne
    {
        return $this->hasOne(NomorSequence::class);
    }

    public function tahunPelajaran(): BelongsTo
    {
        return $this->belongsTo(TahunPelajaran::class, 'scope_id');
    }

    public function jalurPendaftaran(): BelongsTo
    {
        return $this->belongsTo(JalurPendaftaran::class, 'scope_id');
    }

    public function gelombangPendaftaran(): BelongsTo
    {
        return $this->belongsTo(GelombangPendaftaran::class, 'scope_id');
    }

    public function getScopeLabelAttribute(): string
    {
        return match ($this->scope_type) {
            self::SCOPE_TAHUN => $this->tahunPelajaran?->nama ?? 'Tahun tidak ditemukan',
            self::SCOPE_JALUR => $this->jalurPendaftaran?->nama ?? 'Jalur tidak ditemukan',
            self::SCOPE_GELOMBANG => $this->gelombangPendaftaran?->nama ?? 'Gelombang tidak ditemukan',
            default => 'Global',
        };
    }
}
