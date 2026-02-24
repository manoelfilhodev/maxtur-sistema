<?php

namespace App\Http\Controllers\Painel\Operador;

use App\Http\Controllers\Controller;
use App\Models\SolicitacaoAtribuicao;
use App\Models\SolicitacaoViagem;
use App\Models\User;
use App\Models\Veiculo;
use App\Services\TenantContext;
use Illuminate\Http\Request;

class SolicitacaoController extends Controller
{
    public function __construct(private TenantContext $tenantContext) {}

    public function index(Request $request)
    {
        $operadorId = $this->tenantContext->operadorId($request->user());
        $query = SolicitacaoViagem::query()
            ->with(['cliente:id,nome_fantasia,razao_social', 'atribuicoes.veiculo:id,placa', 'atribuicoes.motorista:id,name'])
            ->where('operador_id', $operadorId)
            ->latest('id');

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        $solicitacoes = $query->paginate(20);

        return view('painel.operador.solicitacoes.index', compact('solicitacoes'));
    }

    public function show(Request $request, int $id)
    {
        $operadorId = $this->tenantContext->operadorId($request->user());

        $solicitacao = SolicitacaoViagem::query()
            ->with(['cliente:id,nome_fantasia,razao_social', 'passageiros:id,nome', 'atribuicoes.veiculo:id,placa,modelo', 'atribuicoes.motorista:id,name'])
            ->where('operador_id', $operadorId)
            ->findOrFail($id);

        $veiculos = Veiculo::query()->where('operador_id', $operadorId)->orderBy('placa')->get(['id', 'placa', 'modelo']);
        $motoristas = User::query()->where('operador_id', $operadorId)->where('role', 'motorista')->orderBy('name')->get(['id', 'name']);

        return view('painel.operador.solicitacoes.show', compact('solicitacao', 'veiculos', 'motoristas'));
    }

    public function updateStatus(Request $request, int $id)
    {
        $request->validate([
            'status' => ['required', 'in:aberta,em_analise,aprovada,programada,realizada,cancelada,rejeitada'],
        ]);

        $solicitacao = SolicitacaoViagem::query()
            ->where('operador_id', $this->tenantContext->operadorId($request->user()))
            ->findOrFail($id);

        $solicitacao->update(['status' => $request->string('status')]);

        return back()->with('success', 'Status atualizado com sucesso.');
    }

    public function atribuir(Request $request, int $id)
    {
        $request->validate([
            'veiculo_id' => ['required', 'integer', 'exists:veiculos,id'],
            'motorista_id' => ['required', 'integer', 'exists:users,id'],
        ]);

        $operadorId = $this->tenantContext->operadorId($request->user());

        $solicitacao = SolicitacaoViagem::query()
            ->where('operador_id', $operadorId)
            ->findOrFail($id);

        $veiculo = Veiculo::query()->where('operador_id', $operadorId)->findOrFail($request->integer('veiculo_id'));
        $motorista = User::query()->where('operador_id', $operadorId)->where('role', 'motorista')->findOrFail($request->integer('motorista_id'));

        SolicitacaoAtribuicao::create([
            'operador_id' => $operadorId,
            'solicitacao_id' => $solicitacao->id,
            'veiculo_id' => $veiculo->id,
            'motorista_id' => $motorista->id,
            'atribuido_por' => $request->user()->id,
            'atribuido_em' => now(),
        ]);

        if (!in_array($solicitacao->status, ['realizada', 'cancelada', 'rejeitada'], true)) {
            $solicitacao->update(['status' => 'programada']);
        }

        return back()->with('success', 'Atribuicao registrada com sucesso.');
    }
}

