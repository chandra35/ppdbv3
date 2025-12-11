<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Gtk extends Model
{
    use HasUuids;

    protected $table = 'gtk';
    
    protected $fillable = [
        'nama',
        'nip',
        'jabatan',
        'bidang_keahlian',
        'status',
    ];

    public $timestamps = true;

    // Relation to Verifikator (PPDB)
    public function verifikator(): HasOne
    {
        return $this->hasOne(Verifikator::class, 'gtk_id');
    }
}
