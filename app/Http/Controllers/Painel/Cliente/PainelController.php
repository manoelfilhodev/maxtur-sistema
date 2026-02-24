<?php

namespace App\Http\Controllers\Painel\Cliente;

use App\Http\Controllers\Controller;
use App\Models\AtrasoPassageiro;
use App\Models\AtrasoViagem;
use App\Models\SolicitacaoViagem;
use App\Services\TenantContext;
use Illuminate\Http\Request;

class PainelController extends Controller
{
    public function __construct(private TenantContext $tenantContext) {}

    public function solicitacoes(Request $request)
    {
        $user = $request->user();
        if (!$user->cliente_id) {
            abort(403, 'Usuario sem cliente vinculado.');
        }

        $query = SolicitacaoViagem::query()
            ->with(['passageiros:id,nome', 'atribuicoes.veiculo:id,placa', 'atribuicoes.motorista:id,name'])
            ->where('operador_id', $this->tenantContext->operadorId($user))
            ->where('cliente_id', $user->cliente_id)
            ->latest('id');

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        if ($request->filled('data_inicio')) {
            $query->whereDate('data_hora', '>=', $request->string('data_inicio'));
        }

        if ($request->filled('data_fim')) {
            $query->whereDate('data_hora', '<=', $request->string('data_fim'));
        }

        $solicitacoes = $query->paginate(20);

        return view('painel.cliente.solicitacoes.index', compact('solicitacoes'));
    }

    public function atrasos(Request $request)
    {
        $user = $request->user();
        if (!$user->cliente_id) {
            abort(403, 'Usuario sem cliente vinculado.');
        }

        $operadorId = $this->tenantContext->operadorId($user);
        $clienteId = (int) $user->cliente_id;

        $atrasosViagem = AtrasoViagem::query()
            ->with(['solicitacao:id,origem,destino,data_hora'])
            ->where('operador_id', $operadorId)
            ->where('cliente_id', $clienteId)
            ->latest('id')
            ->paginate(20, ['*'], 'viagem_page');

        $atrasosPassageiro = AtrasoPassageiro::query()
            ->with(['solicitacao:id,origem,destino,data_hora', 'passageiro:id,nome'])
            ->where('operador_id', $operadorId)
            ->where('cliente_id', $clienteId)
            ->latest('id')
            ->paginate(20, ['*'], 'passageiro_page');

        return view('painel.cliente.atrasos.index', compact('atrasosViagem', 'atrasosPassageiro'));
    }
}

