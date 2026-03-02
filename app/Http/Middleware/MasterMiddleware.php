<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class MasterMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (!$user || !$user->isMaster()) {
            abort(403, 'Acesso restrito ao MASTER.');
        }

        return $next($request);
    }
}

