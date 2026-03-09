<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * Usage in routes:
     *   Route::middleware('role:admin')->group(...);
     *   Route::middleware('role:admin,sub-admin')->group(...);
     *
     * @param string $roles Comma-separated list of allowed roles.
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(Response::HTTP_UNAUTHORIZED, 'Unauthenticated.');
        }

        if (! in_array($user->role, $roles, true)) {
            abort(Response::HTTP_FORBIDDEN, 'Access denied: insufficient role.');
        }

        return $next($request);
    }
}
