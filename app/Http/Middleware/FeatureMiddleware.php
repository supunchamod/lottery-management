<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class FeatureMiddleware
{
    /**
     * Handle an incoming request.
     *
     * Usage in routes:
     *   Route::middleware('feature:daily-sales')->group(...);
     *
     * Admins bypass this check (see User::hasFeature). Sub-admins are
     * blocked unless the feature is active for them in permission_user.
     */
    public function handle(Request $request, Closure $next, string $feature): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(Response::HTTP_UNAUTHORIZED, 'Unauthenticated.');
        }

        if (! $user->hasFeature($feature)) {
            abort(Response::HTTP_FORBIDDEN, 'Access denied: this feature is not enabled for your account.');
        }

        return $next($request);
    }
}
