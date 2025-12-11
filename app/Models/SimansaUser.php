<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Model untuk mengakses data User dari database simansav3
 */
class SimansaUser extends Model
{
    protected $connection = 'simansav3';
    protected $table = 'users';
    
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'name',
        'email',
    ];

    /**
     * Get GTK profile
     */
    public function gtk()
    {
        return $this->hasOne(SimansaGtk::class, 'user_id');
    }
}
