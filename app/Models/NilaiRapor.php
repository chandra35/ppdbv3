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
    ];

    protected $casts = [
        'semester' => 'integer',
        'matematika' => 'integer',
        'ipa' => 'integer',
        'ips' => 'integer',
        'rata_rata' => 'decimal:2',
    ];

    // Relations
    public function calonSiswa(): BelongsTo
    {
        return $this->belongsTo(CalonSiswa::class, 'calon_siswa_id');
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

    // Helper methods
    public function isComplete(): bool
    {
        return !empty($this->matematika) && !empty($this->ipa) && !empty($this->ips);
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
