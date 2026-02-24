<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Solicitacoes\AdminAtribuirSolicitacaoRequest;
use App\Http\Requests\Api\Solicitacoes\AdminUpdateSolicitacaoStatusRequest;
use App\Models\SolicitacaoAtribuicao;
use App\Models\SolicitacaoViagem;
use App\Models\User;
use App\Models\Veiculo;
use App\Services\TenantContext;
use Illuminate\Http\Request;

class AdminSolicitacaoController extends Controller
{
    public function __construct(private TenantContext $tenantContext) {}

    /**
     * Listar solicitacoes (admin)
     *
     * Lista solicitacoes do operador autenticado.
     *
     * @group Admin
     * @authenticated
     *
     * @queryParam status string Filtro por status. Example: programada
     *
     * @response 200 {"ok": true, "message": "Solicitacoes listadas", "data": {"current_page": 1, "data": []}}
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $operadorId = $this->tenantContext->operadorId($user);

        $query = SolicitacaoViagem::query()
            ->with(['cliente:id,nome_fantasia,razao_social', 'passageiros:id,nome', 'atribuicoes.veiculo:id,placa,modelo', 'atribuicoes.motorista:id,name'])
            ->where('operador_id', $operadorId)
            ->orderByDesc('id');

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        return response()->json([
            'ok' => true,
            'message' => 'Solicitacoes listadas',
            'data' => $query->paginate(30),
        ]);
    }

    /**
     * Atualizar status de solicitacao
     *
     * Altera status de uma solicitacao do operador.
     *
     * @group Admin
     * @authenticated
     *
     * @urlParam id integer required ID da solicitacao. Example: 101
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

        if (!$solicitacao) {
            return response()->json([
                'ok' => false,
                'message' => 'Solicitacao nao encontrada.',
                'data' => null,
            ], 404);
        }

        $solicitacao->update(['status' => $request->validated('status')]);

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
     * @authenticated
     *
     * @urlParam id integer required ID da solicitacao. Example: 101
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

        if (!$solicitacao) {
            return response()->json([
                'ok' => false,
                'message' => 'Solicitacao nao encontrada.',
                'data' => null,
            ], 404);
        }

        $veiculo = Veiculo::query()
            ->where('operador_id', $operadorId)
            ->find($data['veiculo_id']);
        if (!$veiculo) {
            return response()->json([
                'ok' => false,
                'message' => 'Veiculo fora do escopo do operador.',
                'data' => null,
            ], 422);
        }

        $motorista = User::query()
            ->where('operador_id', $operadorId)
            ->where('role', 'motorista')
            ->find($data['motorista_id']);
        if (!$motorista) {
            return response()->json([
                'ok' => false,
                'message' => 'Motorista fora do escopo do operador.',
                'data' => null,
            ], 422);
        }

        $atribuicao = SolicitacaoAtribuicao::create([
            'operador_id' => $operadorId,
            'solicitacao_id' => $solicitacao->id,
            'veiculo_id' => $veiculo->id,
            'motorista_id' => $motorista->id,
            'atribuido_por' => $user->id,
            'atribuido_em' => now(),
        ]);

        if (!in_array($solicitacao->status, ['realizada', 'cancelada', 'rejeitada'], true)) {
            $solicitacao->update(['status' => 'programada']);
        }

        return response()->json([
            'ok' => true,
            'message' => 'Atribuicao registrada com sucesso',
            'data' => $atribuicao->load(['veiculo:id,placa,modelo', 'motorista:id,name']),
        ]);
    }
}
