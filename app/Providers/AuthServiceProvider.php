<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\User;
use App\Models\Role;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Gate untuk Admin - bisa akses semua
        Gate::define('admin', function (User $user) {
            return $user->isAdmin();
        });

        // Gate untuk Operator
        Gate::define('operator', function (User $user) {
            return $user->hasRole('operator') || $user->isAdmin();
        });

        // Gate untuk Verifikator
        Gate::define('verifikator', function (User $user) {
            return $user->hasRole('verifikator') || $user->isAdmin();
        });

        // Gate untuk Operator atau Verifikator (keduanya bisa akses menu operator)
        Gate::define('operator-or-verifikator', function (User $user) {
            return $user->hasAnyRole(['operator', 'verifikator']) || $user->isAdmin();
        });

        // Gate untuk HANYA Operator atau Verifikator (tanpa admin) - untuk menu display
        Gate::define('only-operator-or-verifikator', function (User $user) {
            return $user->hasAnyRole(['operator', 'verifikator']) && !$user->isAdmin();
        });

        // Gate untuk akses admin panel (bukan pendaftar)
        Gate::define('admin-panel', function (User $user) {
            return $user->isAdmin() || $user->hasAnyRole(['operator', 'verifikator']);
        });

        // Register Gate untuk setiap permission dari database
        $this->registerPermissionGates();
    }

    /**
     * Register Gates berdasarkan permission yang ada di Role
     */
    protected function registerPermissionGates(): void
    {
        // Get all available permissions
        try {
            $permissions = Role::getAvailablePermissions();
            
            foreach ($permissions as $group => $perms) {
                foreach ($perms as $permissionKey => $permissionLabel) {
                    // Register gate untuk setiap permission
                    Gate::define($permissionKey, function (User $user) use ($permissionKey) {
                        // Admin selalu punya akses
                        if ($user->isAdmin()) {
                            return true;
                        }
                        // Cek permission via hasPermission
                        return $user->hasPermission($permissionKey);
                    });
                }
                
                // Register gate untuk wildcard group (e.g., 'pendaftar.*')
                Gate::define($group . '.*', function (User $user) use ($group) {
                    if ($user->isAdmin()) {
                        return true;
                    }
                    return $user->hasPermission($group . '.*');
                });
            }
        } catch (\Exception $e) {
            // Database belum ready (migration belum jalan)
            \Log::debug('Permission gates not registered: ' . $e->getMessage());
        }
    }
}
