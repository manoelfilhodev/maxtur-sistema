<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles)
    {
        $user = $request->user();

        if (!$user) {
            abort(401, 'Nao autenticado.');
        }

        if ($user->isAdmin()) {
            return $next($request);
        }

        if (!in_array($user->role, $roles, true)) {
            abort(403, 'Acesso nao autorizado para o perfil.');
        }

        return $next($request);
    }
}
