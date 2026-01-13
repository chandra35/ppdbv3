<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TrackUserActivity
{
    /**
     * Handle an incoming request.
     * Updates the last_activity timestamp for authenticated users.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            // Update last_activity only if more than 1 minute since last update
            // to reduce database writes
            $user = Auth::user();
            $now = time();
            
            if (!$user->last_activity || ($now - $user->last_activity) > 60) {
                DB::table('users')
                    ->where('id', $user->id)
                    ->update(['last_activity' => $now]);
            }
        }

        return $next($request);
    }
}
