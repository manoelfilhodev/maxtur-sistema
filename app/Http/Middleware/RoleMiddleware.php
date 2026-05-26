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
            abort(401, 'Não autenticado.');
        }

        $effectiveRoles = [$user->role, strtolower((string) $user->role)];

        if ($user->isMaster()) {
            $effectiveRoles[] = 'MASTER';
            $effectiveRoles[] = 'admin';
        }

        if ($user->isAdmin()) {
            $effectiveRoles[] = 'ADMIN';
            $effectiveRoles[] = 'admin';
        }

        if (method_exists($user, 'isOperador') && $user->isOperador()) {
            $effectiveRoles[] = 'OPERADOR';
            $effectiveRoles[] = 'operador';
        }

        if ($user->isClientAdmin()) {
            $effectiveRoles[] = 'CLIENT_ADMIN';
        }

        if ($user->isClientUser()) {
            $effectiveRoles[] = 'CLIENT_USER';
        }

        if ($user->isCliente()) {
            $effectiveRoles[] = 'cliente';
            $effectiveRoles[] = 'CLIENTE';
        }

        if ($user->isMotorista()) {
            $effectiveRoles[] = 'motorista';
            $effectiveRoles[] = 'MOTORISTA';
        }

        if (strtolower((string) $user->role) === 'funcionario') {
            $effectiveRoles[] = 'funcionario';
        }

        $authorized = collect($roles)->contains(fn ($role) => in_array($role, $effectiveRoles, true));
        if (!$authorized) {
            abort(403, 'Acesso não autorizado para o perfil.');
        }

        return $next($request);
    }
}
