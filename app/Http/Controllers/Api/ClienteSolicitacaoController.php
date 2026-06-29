<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Solicitacoes\ClienteStoreSolicitacaoRequest;
use App\Models\Passageiro;
use App\Models\SolicitacaoViagem;
use App\Services\NotificationService;
use App\Services\TenantContext;
use App\Support\ViagemStatus;
use Illuminate\Http\Request;

class ClienteSolicitacaoController extends Controller
{
    public function __construct(
        private TenantContext $tenantContext,
        private NotificationService $notificationService
    ) {}

    /**
     * Listar solicitacoes do cliente autenticado
     *
     * Retorna somente solicitacoes do cliente vinculado ao usuario.
     *
     * @group Solicitacoes
     *
     * @authenticated
     *
     * @queryParam status string Filtro por status. Example: aberta
     * @queryParam data_inicio date Filtro inicial (Y-m-d). Example: 2026-02-01
     * @queryParam data_fim date Filtro final (Y-m-d). Example: 2026-02-28
     *
     * @response 200 {"ok": true, "message": "Solicitacoes listadas", "data": {"current_page": 1, "data": []}}
     */
    public function index(Request $request)
    {
        $request->validate([
            'status' => ['nullable', 'in:'.implode(',', ViagemStatus::all())],
            'data_inicio' => ['nullable', 'date'],
            'data_fim' => ['nullable', 'date', 'after_or_equal:data_inicio'],
            'natureza' => ['nullable', 'in:programada,extra'],
            'tipo_periodo' => ['nullable', 'in:diario,mensal,esporadico'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);
        $user = $request->user();
        if (! $user->cliente_id) {
            return response()->json([
                'ok' => false,
                'message' => 'Usuario nao possui cliente vinculado.',
                'data' => null,
            ], 403);
        }

        $query = SolicitacaoViagem::query()
            ->with(['cliente:id,razao_social,nome_fantasia', 'passageiros:id,nome', 'ultimaAtribuicao.veiculo:id,placa,modelo', 'ultimaAtribuicao.motorista:id,name'])
            ->where('operador_id', $this->tenantContext->operadorId($user))
            ->where('cliente_id', $user->cliente_id)
            ->orderByDesc('id');

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        if ($request->filled('data_inicio')) {
            $query->whereDate('data_hora', '>=', $request->string('data_inicio'));
        }

        if ($request->filled('data_fim')) {
            $query->whereDate('data_hora', '<=', $request->string('data_fim'));
        }
        if ($request->filled('natureza')) {
            $query->where('natureza', $request->string('natureza'));
        }
        if ($request->filled('tipo_periodo')) {
            $query->where('tipo_periodo', $request->string('tipo_periodo'));
        }

        return response()->json([
            'ok' => true,
            'message' => 'Solicitacoes listadas',
            'data' => $query->paginate($request->integer('per_page', 20)),
        ]);
    }

    /**
     * Criar solicitacao de viagem (cliente)
     *
     * Cria solicitacao com status inicial solicitada e notifica admins do operador.
     *
     * @group Solicitacoes
     *
     * @authenticated
     *
     * @bodyParam origem string required Local de origem. Example: Matriz
     * @bodyParam destino string required Local de destino. Example: Aeroporto
     * @bodyParam data_hora datetime required Data/hora da viagem. Example: 2026-02-25 08:00:00
     * @bodyParam passageiros_previstos integer Quantidade prevista. Example: 12
     * @bodyParam observacao string Observacao opcional. Example: Prioridade alta
     * @bodyParam passageiro_ids array IDs dos passageiros do cliente.
     *
     * @response 201 {"ok": true, "message": "Solicitacao criada com sucesso", "data": {"id": 101, "status": "solicitada"}}
     */
    public function store(ClienteStoreSolicitacaoRequest $request)
    {
        $user = $request->user();
        if (! $user->cliente_id) {
            return response()->json([
                'ok' => false,
                'message' => 'Usuario nao possui cliente vinculado.',
                'data' => null,
            ], 403);
        }

        $operadorId = $this->tenantContext->operadorId($user);
        $data = $request->validated();
        $ids = collect($data['passageiro_ids'] ?? [])->unique()->values();
        $validIds = $ids->isEmpty() ? collect() : Passageiro::query()
            ->where('operador_id', $operadorId)
            ->where('cliente_id', $user->cliente_id)
            ->whereIn('id', $ids)
            ->pluck('id');

        if ($validIds->count() !== $ids->count()) {
            return response()->json([
                'ok' => false,
                'message' => 'Um ou mais passageiros não pertencem ao cliente autenticado.',
                'data' => ['errors' => ['passageiro_ids' => ['Revise os passageiros selecionados.']]],
            ], 422);
        }

        $solicitacao = SolicitacaoViagem::create([
            'operador_id' => $operadorId,
            'cliente_id' => $user->cliente_id,
            'origem' => $data['origem'],
            'destino' => $data['destino'],
            'data_hora' => $data['data_hora'],
            'passageiros_previstos' => $data['passageiros_previstos'] ?? 0,
            'observacao' => $data['observacao'] ?? null,
            'status' => ViagemStatus::SOLICITADA,
            'natureza' => $data['natureza'] ?? 'programada',
            'tipo_periodo' => $data['tipo_periodo'] ?? 'esporadico',
        ]);

        if ($ids->isNotEmpty()) {
            $solicitacao->passageiros()->syncWithPivotValues($validIds->all(), [
                'operador_id' => $operadorId,
            ]);
        }

        $this->notificationService->notifyAdmins(
            $operadorId,
            'VIAGEM_SOLICITADA',
            'Nova solicitacao de viagem',
            'Cliente abriu solicitacao #'.$solicitacao->id,
            ['solicitacao_id' => $solicitacao->id, 'cliente_id' => $user->cliente_id]
        );

        return response()->json([
            'ok' => true,
            'message' => 'Solicitacao criada com sucesso',
            'data' => $solicitacao->load('passageiros:id,nome'),
        ], 201);
    }

    public function show(Request $request, int $id)
    {
        $user = $request->user();
        if (! $user->cliente_id) {
            return response()->json(['ok' => false, 'message' => 'Usuario nao possui cliente vinculado.', 'data' => null], 403);
        }

        $solicitacao = SolicitacaoViagem::query()
            ->where('operador_id', $this->tenantContext->operadorId($user))
            ->where('cliente_id', $user->cliente_id)
            ->with([
                'cliente:id,razao_social,nome_fantasia',
                'passageiros:id,nome',
                'ultimaAtribuicao.veiculo:id,placa,modelo,tipo,capacidade_passageiros,status_operacional',
                'ultimaAtribuicao.motorista:id,name,telefone',
                'ultimoChecklist' => fn ($query) => $query->select([
                    'checklists.id', 'checklists.solicitacao_id', 'checklists.status',
                    'checklists.resultado', 'checklists.started_at', 'checklists.finished_at',
                ]),
                'atrasosViagem:id,solicitacao_id,minutos_atraso,motivo,ocorrido_em,created_at',
                'atrasosPassageiro:id,solicitacao_id,passageiro_id,minutos_atraso,motivo,ocorrido_em,created_at',
                'ocorrencias:id,solicitacao_id,tipo,descricao,ocorrido_em,registrado_em,created_at',
            ])
            ->find($id);

        if (! $solicitacao) {
            return response()->json(['ok' => false, 'message' => 'Solicitação não encontrada.', 'data' => null], 404);
        }

        return response()->json(['ok' => true, 'message' => 'Detalhes da solicitação', 'data' => $solicitacao]);
    }
}
