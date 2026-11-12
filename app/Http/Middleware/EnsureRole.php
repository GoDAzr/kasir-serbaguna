<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Pembatas akses berdasarkan role.
 * Pemakaian di route: ->middleware(EnsureRole::class . ':admin')
 */
class EnsureRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user || ! (in_array($user->role, $roles, true) || ($user->role === 'owner' && in_array('admin', $roles, true)))) {
            return response()->json(['message' => 'Anda tidak punya akses ke fitur ini.'], 403);
        }

        return $next($request);
    }
}
