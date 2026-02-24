<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Checklist\ChecklistFinalizeRequest;
use App\Http\Requests\Api\Checklist\ChecklistRespostasRequest;
use App\Http\Requests\Api\Checklist\ChecklistStartRequest;
use App\Models\Checklist;
use App\Models\User;
use App\Models\Veiculo;
use App\Services\ChecklistWorkflowService;
use App\Services\TenantContext;
use Illuminate\Http\Request;
use InvalidArgumentException;

class ChecklistController extends Controller
{
    public function __construct(
        private ChecklistWorkflowService $workflow,
        private TenantContext $tenantContext
    ) {}

    /**
     * Iniciar checklist
     *
     * Cria uma execucao de checklist para veiculo e motorista do operador.
     *
     * @group Checklist
     * @authenticated
     *
     * @bodyParam veiculo_id integer required ID do veiculo. Example: 1
     * @bodyParam motorista_id integer required ID do motorista. Example: 2
     *
     * @response 201 {"ok": true, "message": "Checklist iniciado", "data": {"id": 10, "status": "em_andamento"}}
     */
    public function iniciar(ChecklistStartRequest $request)
    {
        $user = $request->user();
        $operadorId = $this->tenantContext->operadorId($user);
        $data = $request->validated();

        $veiculo = Veiculo::query()
            ->whereKey($data['veiculo_id'])
            ->where('operador_id', $operadorId)
            ->first();
        if (!$veiculo) {
            return response()->json([
                'ok' => false,
                'message' => 'Veiculo nao pertence ao operador do usuario.',
                'data' => null,
            ], 422);
        }

        $motorista = User::query()
            ->whereKey($data['motorista_id'])
            ->where('operador_id', $operadorId)
            ->first();
        if (!$motorista) {
            return response()->json([
                'ok' => false,
                'message' => 'Motorista nao pertence ao operador do usuario.',
                'data' => null,
            ], 422);
        }

        $checklist = $this->workflow->iniciar($user, $data);

        return response()->json([
            'ok' => true,
            'message' => 'Checklist iniciado',
            'data' => $checklist,
        ], 201);
    }

    /**
     * Salvar respostas do checklist
     *
     * Recebe lote de respostas por codigo de item.
     * Para status falha, observacao e foto_base64 sao obrigatorios.
     *
     * @group Checklist
     * @authenticated
     *
     * @urlParam id integer required ID do checklist. Example: 10
     * @bodyParam respostas array required Lista de respostas.
     * @bodyParam respostas[].codigo integer required Codigo do item. Example: 1
     * @bodyParam respostas[].status string required Status do item. Example: ok
     * @bodyParam respostas[].observacao string Observacao do item. Example: Pneu com desgaste
     * @bodyParam respostas[].foto_base64 string Foto em base64. Example: data:image/jpeg;base64,/9j/4AAQSk...
     *
     * @response 200 {"ok": true, "message": "Respostas salvas com sucesso"}
     */
    public function respostas(ChecklistRespostasRequest $request, int $id)
    {
        $checklist = $this->resolveChecklist($request->user(), $id);
        if (!$checklist) {
            return response()->json([
                'ok' => false,
                'message' => 'Checklist nao encontrado no escopo do operador.',
                'data' => null,
            ], 404);
        }

        try {
            $this->workflow->salvarRespostas($request->user(), $checklist, $request->validated('respostas'));
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'ok' => false,
                'message' => $e->getMessage(),
                'data' => null,
            ], 422);
        }

        return response()->json([
            'ok' => true,
            'message' => 'Respostas salvas com sucesso',
            'data' => $checklist->fresh('respostas'),
        ]);
    }

    /**
     * Finalizar checklist
     *
     * Finaliza checklist somente se todos os itens ativos estiverem respondidos.
     *
     * @group Checklist
     * @authenticated
     *
     * @urlParam id integer required ID do checklist. Example: 10
     *
     * @response 200 {"ok": true, "message": "Checklist finalizado com sucesso", "data": {"status": "finalizado", "resultado": "apto"}}
     * @response 422 {"ok": false, "message": "Nao e possivel finalizar checklist com itens sem resposta.", "data": null}
     */
    public function finalizar(ChecklistFinalizeRequest $request, int $id)
    {
        $checklist = $this->resolveChecklist($request->user(), $id);
        if (!$checklist) {
            return response()->json([
                'ok' => false,
                'message' => 'Checklist nao encontrado no escopo do operador.',
                'data' => null,
            ], 404);
        }

        try {
            $checklist = $this->workflow->finalizar($request->user(), $checklist);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'ok' => false,
                'message' => $e->getMessage(),
                'data' => null,
            ], 422);
        }

        return response()->json([
            'ok' => true,
            'message' => 'Checklist finalizado com sucesso',
            'data' => $checklist->load(['respostas', 'veiculo', 'motorista']),
        ]);
    }

    private function resolveChecklist($user, int $id): ?Checklist
    {
        $query = Checklist::query()
            ->where('id', $id)
            ->where('operador_id', $this->tenantContext->operadorId($user));

        if ($user->isMotorista()) {
            $query->where('motorista_id', $user->id);
        }

        return $query->first();
    }
}
