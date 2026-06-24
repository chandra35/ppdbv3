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
            return $user->canAccessAdminPanel();
        });

        // Gate untuk HANYA Operator atau Verifikator (tanpa admin) - untuk menu display
        Gate::define('only-operator-or-verifikator', function (User $user) {
            return $user->canAccessAdminPanel() && !$user->isAdmin();
        });

        // Gate untuk akses admin panel (berdasarkan can_access_admin di role)
        // Penguji-only users go to their own portal, not admin panel
        Gate::define('admin-panel', function (User $user) {
            if ($user->hasRole('penguji') && !$user->isAdmin() && !$user->hasAnyRole(['operator', 'verifikator'])) {
                return false;
            }
            return $user->canAccessAdminPanel();
        });

        // Gate untuk Penguji - akses menu portal penguji
        Gate::define('penguji-panel', function (User $user) {
            return $user->hasRole('penguji') || $user->isAdmin();
        });

        Gate::define('viewLogViewer', function (?User $user) {
            if (!$user || !$user->canAccessAdminPanel()) {
                return false;
            }

            return $user->isAdmin() || $user->hasPermission('logs.view');
        });

        Gate::define('downloadLogFile', function (?User $user, mixed $file = null) {
            return $this->canManageLogViewer($user, 'logs.view');
        });

        Gate::define('downloadLogFolder', function (?User $user, mixed $folder = null) {
            return $this->canManageLogViewer($user, 'logs.view');
        });

        Gate::define('deleteLogFile', function (?User $user, mixed $file = null) {
            return $this->canManageLogViewer($user, 'logs.clear');
        });

        Gate::define('deleteLogFolder', function (?User $user, mixed $folder = null) {
            return $this->canManageLogViewer($user, 'logs.clear');
        });

        // Register Gate untuk setiap permission dari database
        $this->registerPermissionGates();
    }

    protected function canManageLogViewer(?User $user, string $permission): bool
    {
        if (!$user || !$user->canAccessAdminPanel()) {
            return false;
        }

        return $user->isAdmin() || $user->hasPermission($permission);
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
                        // Penguji-only users should not access admin permissions
                        if ($user->hasRole('penguji') && !$user->hasAnyRole(['operator', 'verifikator'])) {
                            return false;
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
