<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureRole
{
    /**
     * Handle an incoming request.
     * Usage in routes: ->middleware('role:hr_admin,line_manager')
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $roles
     * @return mixed
     */
    public function handle(Request $request, Closure $next, string $roles)
    {
        $user = $request->user();
        if (!$user) {
            abort(403, 'Unauthorized');
        }

        $allowed = array_map('trim', explode(',', $roles));
        if (!in_array($user->role, $allowed)) {
            abort(403, 'Forbidden');
        }

        return $next($request);
    }
}
