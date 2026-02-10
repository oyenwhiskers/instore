<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        $user = $request->user();

        $roles = array_map('trim', explode(',', $role));
        if (!$user || !in_array($user->role, $roles, true)) {
            abort(403, 'Unauthorized');
        }

        return $next($request);
    }
}
