<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    /**
     * Usage example:
     * ->middleware('ensure.role:Admin,Super Admin')
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = $request->user();

        if (!$user) {
            abort(401, 'Unauthorized');
        }

        // If user has no role at all, block clearly (common dev mistake)
        if (method_exists($user, 'getRoleNames') && $user->getRoleNames()->count() === 0) {
            abort(403, 'Your account has no role assigned. Ask Super Admin to assign a role.');
        }

        // If roles were specified, enforce
        if (!empty($roles)) {
            if (!$user->hasAnyRole($roles)) {
                abort(403, 'You do not have permission to access this area.');
            }
        }

        return $next($request);
    }
}
