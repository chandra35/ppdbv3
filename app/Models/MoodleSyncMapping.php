<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MoodleSyncMapping extends Model
{
    use HasUuids;

    protected $fillable = [
        'tahun_pelajaran_id',
        'jalur_pendaftaran_id',
        'gelombang_pendaftaran_id',
        'moodle_cohort_id',
        'moodle_category_id',
        'moodle_course_ids',
        'moodle_lastname_template',
        'moodle_password_mode',
        'moodle_password_custom',
        'moodle_email_mode',
        'moodle_email_domain',
        'is_active',
        'keterangan',
    ];

    protected $casts = [
        'moodle_course_ids' => 'array',
        'is_active' => 'boolean',
    ];

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
}
