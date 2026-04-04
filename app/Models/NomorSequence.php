<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NomorSequence extends Model
{
    use HasUuids;

    protected $fillable = [
        'nomor_rule_id',
        'last_number',
        'last_generated_value',
        'last_generated_at',
    ];

    protected $casts = [
        'last_number' => 'integer',
        'last_generated_at' => 'datetime',
    ];

    public function rule(): BelongsTo
    {
        return $this->belongsTo(NomorRule::class, 'nomor_rule_id');
    }
}
