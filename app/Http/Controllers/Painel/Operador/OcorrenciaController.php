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
            'data_ocorrencia' => ['required', 'date'],
            'hora_ocorrencia' => ['required', 'date_format:H:i'],
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
            'ocorrido_em' => $data['data_ocorrencia'].' '.$data['hora_ocorrencia'].':00',
            'registrado_por' => $request->user()->id,
            'registrado_em' => now(),
        ]);

        return back()->with('success', 'Ocorrência registrada com sucesso.');
    }
}
