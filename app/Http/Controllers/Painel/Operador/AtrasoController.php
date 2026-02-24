<?php

namespace App\Http\Controllers\Painel\Operador;

use App\Http\Controllers\Controller;
use App\Models\AtrasoPassageiro;
use App\Models\AtrasoViagem;
use App\Models\Passageiro;
use App\Models\SolicitacaoViagem;
use App\Services\TenantContext;
use Illuminate\Http\Request;

class AtrasoController extends Controller
{
    public function __construct(private TenantContext $tenantContext) {}

    public function index(Request $request)
    {
        $operadorId = $this->tenantContext->operadorId($request->user());
        $solicitacoes = SolicitacaoViagem::query()
            ->where('operador_id', $operadorId)
            ->latest('id')
            ->limit(100)
            ->get(['id', 'cliente_id', 'origem', 'destino', 'status']);

        $atrasosViagem = AtrasoViagem::query()
            ->with(['solicitacao:id,origem,destino', 'cliente:id,nome_fantasia,razao_social'])
            ->where('operador_id', $operadorId)
            ->latest('id')
            ->paginate(20, ['*'], 'viagem_page');

        $atrasosPassageiro = AtrasoPassageiro::query()
            ->with(['solicitacao:id,origem,destino', 'passageiro:id,nome'])
            ->where('operador_id', $operadorId)
            ->latest('id')
            ->paginate(20, ['*'], 'passageiro_page');

        return view('painel.operador.atrasos.index', compact('solicitacoes', 'atrasosViagem', 'atrasosPassageiro'));
    }

    public function storeViagem(Request $request, int $id)
    {
        $request->validate([
            'minutos_atraso' => ['required', 'integer', 'min:1'],
            'motivo' => ['nullable', 'string'],
        ]);

        $solicitacao = SolicitacaoViagem::query()
            ->where('operador_id', $this->tenantContext->operadorId($request->user()))
            ->findOrFail($id);

        AtrasoViagem::create([
            'operador_id' => $solicitacao->operador_id,
            'cliente_id' => $solicitacao->cliente_id,
            'solicitacao_id' => $solicitacao->id,
            'minutos_atraso' => $request->integer('minutos_atraso'),
            'motivo' => $request->input('motivo'),
            'registrado_por' => $request->user()->id,
        ]);

        return back()->with('success', 'Atraso de viagem registrado com sucesso.');
    }

    public function storePassageiro(Request $request, int $id)
    {
        $request->validate([
            'passageiro_id' => ['required', 'integer', 'exists:passageiros,id'],
            'minutos_atraso' => ['required', 'integer', 'min:1'],
            'motivo' => ['nullable', 'string'],
        ]);

        $solicitacao = SolicitacaoViagem::query()
            ->where('operador_id', $this->tenantContext->operadorId($request->user()))
            ->findOrFail($id);

        $passageiro = Passageiro::query()
            ->where('operador_id', $solicitacao->operador_id)
            ->where('cliente_id', $solicitacao->cliente_id)
            ->findOrFail($request->integer('passageiro_id'));

        AtrasoPassageiro::create([
            'operador_id' => $solicitacao->operador_id,
            'cliente_id' => $solicitacao->cliente_id,
            'solicitacao_id' => $solicitacao->id,
            'passageiro_id' => $passageiro->id,
            'minutos_atraso' => $request->integer('minutos_atraso'),
            'motivo' => $request->input('motivo'),
            'registrado_por' => $request->user()->id,
        ]);

        return back()->with('success', 'Atraso de passageiro registrado com sucesso.');
    }
}

