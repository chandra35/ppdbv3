<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NilaiRapor extends Model
{
    use HasUuids;

    protected $table = 'nilai_rapor';

    protected $fillable = [
        'calon_siswa_id',
        'semester',
        'matematika',
        'ipa',
        'ips',
        'rata_rata',
        'dokumen_path',
        'status_validasi',
        'catatan_validasi',
        'validated_by',
        'validated_at',
    ];

    protected $casts = [
        'semester' => 'integer',
        'matematika' => 'integer',
        'ipa' => 'integer',
        'ips' => 'integer',
        'rata_rata' => 'decimal:2',
        'validated_at' => 'datetime',
    ];

    // Relations
    public function calonSiswa(): BelongsTo
    {
        return $this->belongsTo(CalonSiswa::class, 'calon_siswa_id');
    }

    public function validator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    // Auto-calculate rata-rata before saving
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            $model->rata_rata = ($model->matematika + $model->ipa + $model->ips) / 3;
        });
    }

    // Accessor
    public function getRataRataFormattedAttribute(): string
    {
        return number_format($this->rata_rata, 2);
    }

    public function getDokumenUrlAttribute(): ?string
    {
        if ($this->dokumenRecord?->download_url) {
            return $this->dokumenRecord->download_url;
        }

        if ($this->dokumen_path) {
            return asset('storage/' . $this->dokumen_path);
        }
        return null;
    }

    public function getDokumenRecordAttribute(): ?CalonDokumen
    {
        return CalonDokumen::where('calon_siswa_id', $this->calon_siswa_id)
            ->where('jenis_dokumen', 'rapor_sem_' . $this->semester)
            ->first();
    }

    public function getPreviewUrlAttribute(): ?string
    {
        if ($this->dokumenRecord?->preview_url) {
            return $this->dokumenRecord->preview_url;
        }

        if ($this->dokumen_path) {
            return asset('storage/' . $this->dokumen_path);
        }

        return null;
    }

    public function getDownloadUrlAttribute(): ?string
    {
        if ($this->dokumenRecord?->download_url) {
            return $this->dokumenRecord->download_url;
        }

        return $this->dokumen_url;
    }

    public function getDokumenTypeAttribute(): string
    {
        $mimeType = strtolower((string) ($this->dokumenRecord?->mime_type ?? ''));

        if (str_starts_with($mimeType, 'image/')) {
            return 'image';
        }

        if ($mimeType === 'application/pdf') {
            return 'pdf';
        }

        $path = strtolower((string) ($this->dokumenRecord?->nama_file ?? $this->dokumen_path));
        $extension = pathinfo($path, PATHINFO_EXTENSION);

        return in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp']) ? 'image' : 'pdf';
    }

    public function getStatusValidasiBadgeAttribute(): string
    {
        return match($this->status_validasi) {
            'valid' => '<span class="badge badge-success"><i class="fas fa-check"></i> Valid</span>',
            'invalid' => '<span class="badge badge-danger"><i class="fas fa-times"></i> Invalid</span>',
            default => '<span class="badge badge-warning"><i class="fas fa-clock"></i> Pending</span>',
        };
    }

    // Helper methods
    public function isComplete(): bool
    {
        return !empty($this->matematika) && !empty($this->ipa) && !empty($this->ips);
    }

    public function hasDokumen(): bool
    {
        return !empty($this->dokumen_path) || !empty($this->dokumenRecord?->file_path);
    }

    public function isValidated(): bool
    {
        return $this->status_validasi !== 'pending';
    }

    public function isValid(): bool
    {
        return $this->status_validasi === 'valid';
    }

    // Validation rules
    public static function validationRules(): array
    {
        return [
            'semester' => 'required|integer|min:1|max:5',
            'matematika' => 'required|integer|min:1|max:100',
            'ipa' => 'required|integer|min:1|max:100',
            'ips' => 'required|integer|min:1|max:100',
        ];
    }

    public static function validationMessages(): array
    {
        return [
            'semester.required' => 'Semester harus diisi',
            'semester.min' => 'Semester minimal 1',
            'semester.max' => 'Semester maksimal 5',
            'matematika.required' => 'Nilai Matematika harus diisi',
            'matematika.min' => 'Nilai Matematika minimal 1',
            'matematika.max' => 'Nilai Matematika maksimal 100',
            'ipa.required' => 'Nilai IPA harus diisi',
            'ipa.min' => 'Nilai IPA minimal 1',
            'ipa.max' => 'Nilai IPA maksimal 100',
            'ips.required' => 'Nilai IPS harus diisi',
            'ips.min' => 'Nilai IPS minimal 1',
            'ips.max' => 'Nilai IPS maksimal 100',
        ];
    }
}
