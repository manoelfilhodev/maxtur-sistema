<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\NotificationMvp;
use App\Services\TenantContext;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function __construct(private TenantContext $tenantContext) {}

    /**
     * Listar notificacoes do usuario
     *
     * Retorna notificacoes do usuario autenticado no escopo do operador.
     *
     * @group Notificacoes
     * @authenticated
     *
     * @response 200 {"ok": true, "message": "Notificacoes listadas", "data": {"current_page": 1, "data": []}}
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $notifications = NotificationMvp::query()
            ->where('operador_id', $this->tenantContext->operadorId($user))
            ->whereHas('users', fn ($q) => $q->where('users.id', $user->id))
            ->with(['users' => fn ($q) => $q->where('users.id', $user->id)])
            ->latest('id')
            ->paginate(30);

        return response()->json([
            'ok' => true,
            'message' => 'Notificacoes listadas',
            'data' => $notifications,
        ]);
    }

    /**
     * Marcar notificacao como lida
     *
     * Atualiza read_at no vinculo usuario-notificacao.
     *
     * @group Notificacoes
     * @authenticated
     *
     * @urlParam id integer required ID da notificacao. Example: 1
     *
     * @response 200 {"ok": true, "message": "Notificacao marcada como lida", "data": {"id": 1, "read_at": "2026-02-24 14:00:00"}}
     */
    public function read(Request $request, int $id)
    {
        $user = $request->user();

        $notification = NotificationMvp::query()
            ->where('operador_id', $this->tenantContext->operadorId($user))
            ->whereHas('users', fn ($q) => $q->where('users.id', $user->id))
            ->find($id);

        if (!$notification) {
            return response()->json([
                'ok' => false,
                'message' => 'Notificacao nao encontrada para este usuario.',
                'data' => null,
            ], 404);
        }

        $notification->users()->updateExistingPivot($user->id, ['read_at' => now()]);

        return response()->json([
            'ok' => true,
            'message' => 'Notificacao marcada como lida',
            'data' => ['id' => $notification->id, 'read_at' => now()->toDateTimeString()],
        ]);
    }
}
