<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PengujiMiddleware
{
    /**
     * Handle an incoming request.
     * Middleware untuk role penguji
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated
        if (!auth()->check()) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }
            return redirect()->route('login')
                ->with('warning', 'Silahkan login terlebih dahulu.');
        }

        $user = auth()->user();
        
        // Check if user has penguji role or is admin (admin can access all)
        if (!$user->hasRole('penguji') && !$user->isAdmin()) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Forbidden'], 403);
            }
            return redirect()->route('pendaftar.dashboard')
                ->with('error', 'Maaf, Anda tidak memiliki akses ke halaman penguji.');
        }

        return $next($request);
    }
}
