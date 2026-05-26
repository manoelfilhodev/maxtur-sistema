<?php

namespace App\Http\Controllers\Painel;

use App\Http\Controllers\Controller;
use App\Models\SolicitacaoViagem;
use App\Support\ViagemStatus;

class DashboardController extends Controller
{
    public function index()
    {
        $statusCounts = SolicitacaoViagem::query()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $metricas = [
            'total' => SolicitacaoViagem::query()->count(),
            'solicitadas' => (int) ($statusCounts[ViagemStatus::SOLICITADA] ?? 0),
            'programadas' => (int) ($statusCounts[ViagemStatus::PROGRAMADA] ?? 0) + (int) ($statusCounts[ViagemStatus::CHECKLIST_PENDENTE] ?? 0) + (int) ($statusCounts[ViagemStatus::PRONTA_PARA_EXECUCAO] ?? 0),
            'em_andamento' => (int) ($statusCounts[ViagemStatus::EM_ANDAMENTO] ?? 0),
            'atrasadas' => (int) ($statusCounts[ViagemStatus::ATRASADA] ?? 0),
            'finalizadas' => (int) ($statusCounts[ViagemStatus::FINALIZADA] ?? 0),
            'bloqueadas' => (int) ($statusCounts[ViagemStatus::BLOQUEADA] ?? 0),
        ];

        $ultimasSolicitacoes = SolicitacaoViagem::query()
            ->with(['cliente:id,nome_fantasia,razao_social', 'atribuicoes.veiculo:id,placa', 'atribuicoes.motorista:id,name'])
            ->latest('id')
            ->limit(8)
            ->get();

        return view('painel.dashboard', compact('metricas', 'ultimasSolicitacoes'));
    }
}
