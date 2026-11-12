<?php

namespace App\Http\Middleware;

use App\Support\Tenant;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** Pemakaian di route: ->middleware('perm:produk') */
class EnsurePermission
{
    public function handle(Request $request, Closure $next, string $perm): Response
    {
        if (! Tenant::can($perm)) {
            abort(403, 'Anda tidak punya izin untuk membuka halaman ini.');
        }

        return $next($request);
    }
}
