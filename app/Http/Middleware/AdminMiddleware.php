<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
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
                ->with('warning', 'Silahkan login terlebih dahulu untuk mengakses halaman admin.');
        }

        // Check if user has admin or operator/verifikator role
        $user = auth()->user();
        
        // Allow: admin, operator, verifikator
        if (!$user->isAdmin() && !$user->hasAnyRole(['operator', 'verifikator'])) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Forbidden'], 403);
            }
            return redirect()->route('ppdb.dashboard')
                ->with('error', 'Maaf, Anda tidak memiliki akses ke halaman ini.');
        }

        return $next($request);
    }
}
