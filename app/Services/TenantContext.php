<?php

namespace App\Services;

use App\Models\User;

class TenantContext
{
    public function operadorId(User $user): int
    {
        $operadorId = (int) $user->operador_id;

        if ($operadorId <= 0) {
            abort(403, 'Usuario sem operador vinculado.');
        }

        return $operadorId;
    }

    public function assertClienteScope(User $user, int $clienteId): void
    {
        if ($user->isAdmin()) {
            return;
        }

        if (!$user->isCliente() || (int) $user->cliente_id !== $clienteId) {
            abort(403, 'Acesso nao autorizado ao cliente informado.');
        }
    }
}
