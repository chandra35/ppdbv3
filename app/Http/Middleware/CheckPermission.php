<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  $permission  Permission yang dibutuhkan (e.g., 'pendaftar.view')
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $user = auth()->user();

        if (!$user) {
            return redirect()->route('login')
                ->with('error', 'Silakan login terlebih dahulu.');
        }

        // Admin selalu punya akses
        if ($user->isAdmin()) {
            return $next($request);
        }

        // Cek permission
        if (!$user->hasPermission($permission)) {
            // Jika AJAX request
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki akses untuk fitur ini.'
                ], 403);
            }

            // Redirect ke dashboard dengan pesan error
            return $this->redirectToDashboard($user)
                ->with('error', 'Anda tidak memiliki akses untuk halaman tersebut.');
        }

        return $next($request);
    }

    /**
     * Redirect user ke dashboard sesuai role mereka
     */
    protected function redirectToDashboard($user)
    {
        // Pendaftar → ke pendaftar dashboard
        if ($user->hasRole('pendaftar')) {
            return redirect()->route('pendaftar.dashboard');
        }

        // Penguji → ke penguji dashboard
        if ($user->hasRole('penguji') && !$user->isAdmin()) {
            return redirect()->route('penguji.dashboard');
        }

        // Admin, Operator, Verifikator → ke admin dashboard
        if ($user->isAdmin() || $user->hasAnyRole(['operator', 'verifikator'])) {
            return redirect()->route('admin.dashboard');
        }

        return redirect()->route('ppdb.landing');
    }
}
