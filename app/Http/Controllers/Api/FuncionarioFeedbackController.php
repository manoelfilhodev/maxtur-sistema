<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FuncionarioFeedback;
use App\Services\NotificationService;
use App\Services\TenantContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class FuncionarioFeedbackController extends Controller
{
    public function __construct(
        private TenantContext $tenantContext,
        private NotificationService $notificationService
    ) {}

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tipo' => ['required', 'in:sugestao,critica'],
            'mensagem' => ['required', 'string', 'min:10', 'max:2000'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'ok' => false,
                'message' => 'Dados inválidos.',
                'data' => $validator->errors(),
            ], 422);
        }

        $user = $request->user();
        $operadorId = $this->tenantContext->operadorId($user);
        $data = $validator->validated();

        $feedback = FuncionarioFeedback::query()->create([
            'operador_id' => $operadorId,
            'funcionario_user_id' => (int) $user->id,
            'tipo' => $data['tipo'],
            'mensagem' => $data['mensagem'],
            'status' => 'novo',
        ]);

        $tipoLabel = $data['tipo'] === 'critica' ? 'critica' : 'sugestao';
        $this->notificationService->notifyPanelUsers(
            $operadorId,
            'FUNCIONARIO_FEEDBACK',
            $data['tipo'] === 'critica' ? 'Nova critica de funcionario' : 'Nova sugestao de funcionario',
            "{$user->name} enviou uma {$tipoLabel}.",
            [
                'feedback_id' => $feedback->id,
                'reference_id' => $feedback->id,
                'tipo' => $feedback->tipo,
                'funcionario_user_id' => $user->id,
                'funcionario_nome' => $user->name,
            ]
        );

        return response()->json([
            'ok' => true,
            'message' => 'Feedback enviado com sucesso.',
            'data' => [
                'id' => $feedback->id,
            ],
        ]);
    }
}
