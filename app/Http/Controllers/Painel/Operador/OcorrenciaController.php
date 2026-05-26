<?php

namespace App\Http\Controllers\Painel\Operador;

use App\Http\Controllers\Controller;
use App\Models\OcorrenciaViagem;
use App\Models\SolicitacaoViagem;
use App\Services\TenantContext;
use Illuminate\Http\Request;

class OcorrenciaController extends Controller
{
    public function __construct(private TenantContext $tenantContext) {}

    public function store(Request $request, int $id)
    {
        $data = $request->validate([
            'tipo' => ['required', 'string', 'max:80'],
            'descricao' => ['required', 'string'],
        ]);

        $solicitacao = SolicitacaoViagem::query()
            ->where('operador_id', $this->tenantContext->operadorId($request->user()))
            ->findOrFail($id);

        OcorrenciaViagem::create([
            'operador_id' => $solicitacao->operador_id,
            'cliente_id' => $solicitacao->cliente_id,
            'solicitacao_id' => $solicitacao->id,
            'tipo' => $data['tipo'],
            'descricao' => $data['descricao'],
            'registrado_por' => $request->user()->id,
            'registrado_em' => now(),
        ]);

        return back()->with('success', 'Ocorrência registrada com sucesso.');
    }
}
