<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check()) {
            abort(403, 'Acesso nao autorizado.');
        }

        $user = Auth::user();

        // master sempre tem acesso
        if ((int) ($user->id ?? 0) === 1) {
            return $next($request);
        }

        // regra: admin ativo (compativel com role/cargo/nivel)
        $papel = strtolower((string) ($user->role ?? $user->nivel ?? $user->cargo ?? ''));
        $ativo = (int) ($user->ativo ?? 1);

        if (!in_array($papel, ['admin', 'master'], true) || $ativo !== 1) {
            abort(403, 'Acesso nao autorizado.');
        }

        return $next($request);
    }
}
