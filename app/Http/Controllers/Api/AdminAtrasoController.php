<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Atrasos\StoreAtrasoPassageiroRequest;
use App\Http\Requests\Api\Atrasos\StoreAtrasoViagemRequest;
use App\Models\AtrasoPassageiro;
use App\Models\AtrasoViagem;
use App\Models\Passageiro;
use App\Models\SolicitacaoViagem;
use App\Models\User;
use App\Services\TenantContext;

class AdminAtrasoController extends Controller
{
    public function __construct(private TenantContext $tenantContext) {}

    /**
     * Registrar atraso da viagem
     *
     * Cria registro de atraso no nivel da solicitacao.
     *
     * @group Atrasos
     * @authenticated
     *
     * @urlParam id integer required ID da solicitacao. Example: 101
     * @bodyParam minutos_atraso integer required Minutos de atraso. Example: 20
     * @bodyParam motivo string Motivo do atraso. Example: Transito intenso
     *
     * @response 201 {"ok": true, "message": "Atraso de viagem registrado com sucesso"}
     */
    public function storeViagem(StoreAtrasoViagemRequest $request, int $id)
    {
        $user = $request->user();
        $solicitacao = $this->findSolicitacao($id, $user);
        if (!$solicitacao) {
            return response()->json([
                'ok' => false,
                'message' => 'Solicitacao nao encontrada.',
                'data' => null,
            ], 404);
        }

        $data = $request->validated();
        $atraso = AtrasoViagem::create([
            'operador_id' => $solicitacao->operador_id,
            'cliente_id' => $solicitacao->cliente_id,
            'solicitacao_id' => $solicitacao->id,
            'minutos_atraso' => $data['minutos_atraso'],
            'motivo' => $data['motivo'] ?? null,
            'registrado_por' => $user->id,
        ]);

        return response()->json([
            'ok' => true,
            'message' => 'Atraso de viagem registrado com sucesso',
            'data' => $atraso,
        ], 201);
    }

    /**
     * Registrar atraso de passageiro
     *
     * Cria registro de atraso de um passageiro vinculado a solicitacao.
     *
     * @group Atrasos
     * @authenticated
     *
     * @urlParam id integer required ID da solicitacao. Example: 101
     * @bodyParam passageiro_id integer required ID do passageiro. Example: 55
     * @bodyParam minutos_atraso integer required Minutos de atraso. Example: 10
     * @bodyParam motivo string Motivo do atraso. Example: Nao compareceu no ponto
     *
     * @response 201 {"ok": true, "message": "Atraso de passageiro registrado com sucesso"}
     */
    public function storePassageiro(StoreAtrasoPassageiroRequest $request, int $id)
    {
        $user = $request->user();
        $solicitacao = $this->findSolicitacao($id, $user);
        if (!$solicitacao) {
            return response()->json([
                'ok' => false,
                'message' => 'Solicitacao nao encontrada.',
                'data' => null,
            ], 404);
        }

        $data = $request->validated();
        $passageiro = Passageiro::query()
            ->where('operador_id', $solicitacao->operador_id)
            ->where('cliente_id', $solicitacao->cliente_id)
            ->find($data['passageiro_id']);

        if (!$passageiro) {
            return response()->json([
                'ok' => false,
                'message' => 'Passageiro nao pertence ao escopo da solicitacao.',
                'data' => null,
            ], 422);
        }

        $atraso = AtrasoPassageiro::create([
            'operador_id' => $solicitacao->operador_id,
            'cliente_id' => $solicitacao->cliente_id,
            'solicitacao_id' => $solicitacao->id,
            'passageiro_id' => $passageiro->id,
            'minutos_atraso' => $data['minutos_atraso'],
            'motivo' => $data['motivo'] ?? null,
            'registrado_por' => $user->id,
        ]);

        return response()->json([
            'ok' => true,
            'message' => 'Atraso de passageiro registrado com sucesso',
            'data' => $atraso,
        ], 201);
    }

    private function findSolicitacao(int $id, User $user): ?SolicitacaoViagem
    {
        return SolicitacaoViagem::query()
            ->where('operador_id', $this->tenantContext->operadorId($user))
            ->find($id);
    }
}
