<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        if (! $user->is_active) {
            auth()->logout();
            return redirect()->route('login')->withErrors(['mobile' => 'Your account has been deactivated. Please contact support.']);
        }

        // Super-admin always bypasses role checks
        if ($user->hasRole('super-admin')) {
            return $next($request);
        }

        if (! $user->hasRole($roles)) {
            abort(403, 'Unauthorized access to this module.');
        }

        return $next($request);
    }
}
