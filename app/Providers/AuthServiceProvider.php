<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\User;

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
        // Gate untuk Admin
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
    }
}
