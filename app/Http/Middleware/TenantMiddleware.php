<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class TenantMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (!$user || $user->isMaster()) {
            abort(403, 'Acesso restrito ao ambiente do cliente.');
        }

        $clientId = (int) ($user->client_id ?: $user->cliente_id ?: 0);
        if ($clientId <= 0) {
            abort(403, 'Usuario sem client_id vinculado.');
        }

        $request->attributes->set('client_id', $clientId);

        return $next($request);
    }
}

