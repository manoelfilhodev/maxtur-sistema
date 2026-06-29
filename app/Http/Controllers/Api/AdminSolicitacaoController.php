<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Solicitacoes\AdminAtribuirSolicitacaoRequest;
use App\Http\Requests\Api\Solicitacoes\AdminUpdateSolicitacaoStatusRequest;
use App\Models\SolicitacaoViagem;
use App\Models\User;
use App\Models\Veiculo;
use App\Services\TenantContext;
use App\Services\ViagemOperacionalService;
use App\Support\ViagemStatus;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AdminSolicitacaoController extends Controller
{
    public function __construct(
        private TenantContext $tenantContext,
        private ViagemOperacionalService $viagemOperacional
    ) {}

    /**
     * Listar solicitacoes (admin)
     *
     * Lista solicitacoes do operador autenticado.
     *
     * @group Admin
     *
     * @authenticated
     *
     * @queryParam status string Filtro por status. Example: programada
     *
     * @response 200 {"ok": true, "message": "Solicitacoes listadas", "data": {"current_page": 1, "data": []}}
     */
    public function index(Request $request)
    {
        $request->validate([
            'status' => ['nullable', 'in:'.implode(',', ViagemStatus::all())],
            'data_inicio' => ['nullable', 'date'],
            'data_fim' => ['nullable', 'date', 'after_or_equal:data_inicio'],
            'cliente_id' => ['nullable', 'integer'],
            'motorista_id' => ['nullable', 'integer'],
            'veiculo_id' => ['nullable', 'integer'],
            'natureza' => ['nullable', 'in:programada,extra'],
            'tipo_periodo' => ['nullable', 'in:diario,mensal,esporadico'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);
        $user = $request->user();
        $operadorId = $this->tenantContext->operadorId($user);

        $query = SolicitacaoViagem::query()
            ->with(['cliente:id,nome_fantasia,razao_social', 'passageiros:id,nome', 'ultimaAtribuicao.veiculo:id,placa,modelo', 'ultimaAtribuicao.motorista:id,name'])
            ->where('operador_id', $operadorId)
            ->orderByDesc('id');

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }
        if ($request->filled('data_inicio')) {
            $query->whereDate('data_hora', '>=', $request->date('data_inicio'));
        }
        if ($request->filled('data_fim')) {
            $query->whereDate('data_hora', '<=', $request->date('data_fim'));
        }
        foreach (['cliente_id', 'natureza', 'tipo_periodo'] as $field) {
            if ($request->filled($field)) {
                $query->where($field, $request->input($field));
            }
        }
        if ($request->filled('motorista_id')) {
            $query->whereHas('ultimaAtribuicao', fn ($subquery) => $subquery->where('motorista_id', $request->integer('motorista_id')));
        }
        if ($request->filled('veiculo_id')) {
            $query->whereHas('ultimaAtribuicao', fn ($subquery) => $subquery->where('veiculo_id', $request->integer('veiculo_id')));
        }

        return response()->json([
            'ok' => true,
            'message' => 'Solicitacoes listadas',
            'data' => $query->paginate($request->integer('per_page', 30)),
        ]);
    }

    /**
     * Atualizar status de solicitacao
     *
     * Altera status de uma solicitacao do operador.
     *
     * @group Admin
     *
     * @authenticated
     *
     * @urlParam id integer required ID da solicitacao. Example: 101
     *
     * @bodyParam status string required Novo status. Example: aprovada
     *
     * @response 200 {"ok": true, "message": "Status atualizado com sucesso", "data": {"id": 101, "status": "aprovada"}}
     */
    public function status(AdminUpdateSolicitacaoStatusRequest $request, int $id)
    {
        $user = $request->user();
        $solicitacao = SolicitacaoViagem::query()
            ->where('operador_id', $this->tenantContext->operadorId($user))
            ->find($id);

        if (! $solicitacao) {
            return response()->json([
                'ok' => false,
                'message' => 'Solicitacao nao encontrada.',
                'data' => null,
            ], 404);
        }

        $novoStatus = $request->validated('status');
        if (! ViagemStatus::podeTransicionar($solicitacao->status, $novoStatus)) {
            return response()->json([
                'ok' => false,
                'message' => 'Transição de status não permitida.',
                'data' => ['status_atual' => $solicitacao->status, 'status_solicitado' => $novoStatus],
            ], 422);
        }

        $solicitacao->update(['status' => $novoStatus]);

        return response()->json([
            'ok' => true,
            'message' => 'Status atualizado com sucesso',
            'data' => $solicitacao->fresh(),
        ]);
    }

    /**
     * Atribuir veiculo e motorista
     *
     * Registra atribuicao operacional para a solicitacao.
     *
     * @group Admin
     *
     * @authenticated
     *
     * @urlParam id integer required ID da solicitacao. Example: 101
     *
     * @bodyParam veiculo_id integer required ID do veiculo. Example: 1
     * @bodyParam motorista_id integer required ID do motorista. Example: 2
     *
     * @response 200 {"ok": true, "message": "Atribuicao registrada com sucesso"}
     */
    public function atribuir(AdminAtribuirSolicitacaoRequest $request, int $id)
    {
        $user = $request->user();
        $operadorId = $this->tenantContext->operadorId($user);
        $data = $request->validated();

        $solicitacao = SolicitacaoViagem::query()
            ->where('operador_id', $operadorId)
            ->find($id);

        if (! $solicitacao) {
            return response()->json([
                'ok' => false,
                'message' => 'Solicitacao nao encontrada.',
                'data' => null,
            ], 404);
        }

        $veiculo = Veiculo::query()
            ->where('operador_id', $operadorId)
            ->find($data['veiculo_id']);
        if (! $veiculo) {
            return response()->json([
                'ok' => false,
                'message' => 'Veiculo fora do escopo do operador.',
                'data' => null,
            ], 422);
        }

        $motorista = User::query()
            ->where('operador_id', $operadorId)
            ->whereIn('role', ['motorista', 'MOTORISTA'])
            ->find($data['motorista_id']);
        if (! $motorista) {
            return response()->json([
                'ok' => false,
                'message' => 'Motorista fora do escopo do operador.',
                'data' => null,
            ], 422);
        }

        try {
            $atribuicao = $this->viagemOperacional->atribuir($solicitacao, $veiculo, $motorista, $user);
        } catch (ValidationException $e) {
            return response()->json([
                'ok' => false,
                'message' => collect($e->errors())->flatten()->first(),
                'data' => ['errors' => $e->errors()],
            ], 422);
        }

        return response()->json([
            'ok' => true,
            'message' => 'Atribuicao registrada. A viagem agora aguarda checklist do motorista.',
            'data' => $atribuicao->load(['veiculo:id,placa,modelo', 'motorista:id,name']),
        ]);
    }
}
