<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  mixed ...$roles
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $user = Auth::user();

        if (! $user) {
            return redirect()->route('login');
        }

        // Super admin has access to everything
        if ($user->role === 'super_admin') {
            return $next($request);
        }

        // If no roles specified, allow through
        if (count($roles) === 0) {
            return $next($request);
        }

        // Allow if user role matches any allowed role
        if (in_array($user->role, $roles)) {
            return $next($request);
        }

        abort(403, 'Unauthorized.');
    }
}
