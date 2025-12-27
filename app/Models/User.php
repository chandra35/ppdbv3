<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, HasUuids, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'username',
        'password',
        'photo',
        'phone',
        'plain_password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // Relations
    public function calonSiswa(): HasMany
    {
        return $this->hasMany(CalonSiswa::class);
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'user_roles')
            ->using(UserRole::class)
            ->withTimestamps();
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }

    public function verifikator(): HasOne
    {
        return $this->hasOne(Verifikator::class, 'user_id');
    }

    /**
     * Check if user has a specific role
     */
    public function hasRole(string $roleName): bool
    {
        return $this->roles()->where('name', $roleName)->exists();
    }

    /**
     * Assign a role to the user
     */
    public function assignRole(string $roleName): void
    {
        $role = Role::where('name', $roleName)->first();
        
        if ($role && !$this->hasRole($roleName)) {
            $this->roles()->attach($role->id);
        }
    }

    /**
     * Remove a role from the user
     */
    public function removeRole(string $roleName): void
    {
        $role = Role::where('name', $roleName)->first();
        
        if ($role) {
            $this->roles()->detach($role->id);
        }
    }

    /**
     * Check if user has any of the given roles
     */
    public function hasAnyRole(array $roleNames): bool
    {
        return $this->roles()->whereIn('name', $roleNames)->exists();
    }

    /**
     * Check if user has a specific permission
     */
    public function hasPermission(string $permission): bool
    {
        // Admin has all permissions
        if ($this->hasRole('admin') || $this->hasRole('super-admin')) {
            return true;
        }

        foreach ($this->roles as $role) {
            if ($role->hasPermission($permission)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if user is admin
     */
    public function isAdmin(): bool
    {
        return $this->hasAnyRole(['admin', 'super-admin']) || 
               in_array($this->email, ['admin@ppdb.local', 'administrator@ppdb.local']);
    }

    /**
     * Get user profile image URL for AdminLTE
     */
    public function adminlte_image()
    {
        if ($this->photo && \Storage::disk('public')->exists($this->photo)) {
            return \Storage::url($this->photo);
        }

        // Generate avatar from name
        $initials = strtoupper(substr($this->name, 0, 1));
        $bgColor = '3c8dbc';
        
        return "https://ui-avatars.com/api/?name=" . urlencode($this->name) . 
               "&background=" . $bgColor . 
               "&color=fff&size=200&bold=true&format=svg";
    }

    /**
     * Get user profile URL for AdminLTE
     */
    public function adminlte_profile_url()
    {
        return 'admin.profile.index';
    }
}
