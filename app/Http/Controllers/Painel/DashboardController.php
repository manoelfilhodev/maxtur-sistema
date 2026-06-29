<?php

namespace App\Http\Controllers\Painel;

use App\Http\Controllers\Controller;
use App\Models\Checklist;
use App\Models\Cliente;
use App\Models\OcorrenciaViagem;
use App\Models\SolicitacaoAtribuicao;
use App\Models\SolicitacaoViagem;
use App\Models\User;
use App\Models\Veiculo;
use App\Models\VeiculoManutencao;
use App\Support\ViagemStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $operadorId = (int) ($request->user()->operador_id ?: 1);
        $viagensQuery = $this->viagensFiltradas($request, $operadorId);

        $statusCounts = (clone $viagensQuery)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $statusAtivos = [
            ViagemStatus::PROGRAMADA,
            ViagemStatus::CHECKLIST_PENDENTE,
            ViagemStatus::PRONTA_PARA_EXECUCAO,
            ViagemStatus::EM_ANDAMENTO,
            ViagemStatus::ATRASADA,
        ];

        $atribuicoesAtivas = SolicitacaoAtribuicao::query()
            ->where('operador_id', $operadorId)
            ->whereHas('solicitacao', fn (Builder $query) => $query->whereIn('status', $statusAtivos))
            ->get(['motorista_id', 'veiculo_id']);

        $motoristasAtivos = User::query()
            ->where('operador_id', $operadorId)
            ->where(function (Builder $query) {
                $query->whereIn('role', ['motorista', 'MOTORISTA'])
                    ->orWhereIn('cargo', ['motorista', 'MOTORISTA']);
            })
            ->where('ativo', true);

        $veiculos = Veiculo::query()
            ->where('operador_id', $operadorId)
            ->with('manutencoes')
            ->orderBy('placa')
            ->get();

        $manutencoes = $veiculos->flatMap(function (Veiculo $veiculo) {
            return $veiculo->manutencoes->map(function (VeiculoManutencao $manutencao) use ($veiculo) {
                $manutencao->status_dashboard = $manutencao->calcularStatus((int) $veiculo->km_atual);
                $manutencao->km_restante = (int) $manutencao->km_vencimento - (int) $veiculo->km_atual;

                return $manutencao;
            });
        });

        $cnhVencidas = (clone $motoristasAtivos)->whereDate('cnh_vencimento', '<', today())->count();
        $cnhProximas = (clone $motoristasAtivos)
            ->whereBetween('cnh_vencimento', [today(), today()->copy()->addDays(30)])
            ->count();
        $checklistsPendentes = Checklist::query()
            ->where('operador_id', $operadorId)
            ->whereNotIn('status', ['finalizado', 'concluido'])
            ->count();
        $checklistsHoje = Checklist::query()
            ->where('operador_id', $operadorId)
            ->whereIn('status', ['finalizado', 'concluido'])
            ->whereDate('finished_at', today())
            ->count();
        $ocorrenciasRecentesTotal = OcorrenciaViagem::query()
            ->where('operador_id', $operadorId)
            ->where('registrado_em', '>=', now()->subDay())
            ->count();

        $metricas = [
            'viagens_periodo' => (clone $viagensQuery)->count(),
            'em_andamento' => (int) ($statusCounts[ViagemStatus::EM_ANDAMENTO] ?? 0),
            'programadas' => collect([ViagemStatus::PROGRAMADA, ViagemStatus::CHECKLIST_PENDENTE, ViagemStatus::PRONTA_PARA_EXECUCAO])->sum(fn ($status) => (int) ($statusCounts[$status] ?? 0)),
            'extras' => (clone $viagensQuery)->where('natureza', 'extra')->count(),
            'atrasadas' => (int) ($statusCounts[ViagemStatus::ATRASADA] ?? 0),
            'motoristas_disponiveis' => max(0, (clone $motoristasAtivos)->count() - $atribuicoesAtivas->pluck('motorista_id')->unique()->count()),
            'motoristas_em_viagem' => $atribuicoesAtivas->pluck('motorista_id')->unique()->count(),
            'veiculos_disponiveis' => max(0, $veiculos->where('status_operacional', 'liberado')->count() - $atribuicoesAtivas->pluck('veiculo_id')->unique()->count()),
            'veiculos_bloqueados' => $veiculos->where('status_operacional', 'bloqueado')->count(),
            'checklists_pendentes' => $checklistsPendentes,
            'ocorrencias_recentes' => $ocorrenciasRecentesTotal,
            'manutencoes_vencidas' => $manutencoes->where('status_dashboard', 'vencido')->count(),
            'documentos_atencao' => $cnhVencidas + $cnhProximas,
        ];

        $hoje = $this->viagensFiltradas($request, $operadorId, false)->whereDate('data_hora', today())->count();
        $ontem = $this->viagensFiltradas($request, $operadorId, false)->whereDate('data_hora', today()->subDay())->count();
        $tendenciaViagens = $ontem > 0 ? (int) round((($hoje - $ontem) / $ontem) * 100) : ($hoje > 0 ? 100 : 0);

        $alertas = $this->montarAlertas(
            $metricas,
            $cnhVencidas,
            $cnhProximas,
            $manutencoes,
            $veiculos,
            $ocorrenciasRecentesTotal
        );

        $indicadores = [
            'cnh_vencidas' => $cnhVencidas,
            'cnh_proximas' => $cnhProximas,
            'veiculos_manutencao' => $manutencoes->whereIn('status_dashboard', ['vencido', 'proximo_vencimento'])->pluck('veiculo_id')->unique()->count(),
            'veiculos_bloqueados' => $metricas['veiculos_bloqueados'],
            'veiculos_disponiveis' => $metricas['veiculos_disponiveis'],
            'checklists_pendentes' => $checklistsPendentes,
            'checklists_hoje' => $checklistsHoje,
        ];

        $ultimasSolicitacoes = (clone $viagensQuery)
            ->with([
                'cliente:id,nome_fantasia,razao_social',
                'atribuicoes.veiculo:id,placa',
                'atribuicoes.motorista:id,name',
                'checklists:id,solicitacao_id,status',
            ])
            ->latest('data_hora')
            ->limit(10)
            ->get();

        $ocorrenciasRecentes = OcorrenciaViagem::query()
            ->where('operador_id', $operadorId)
            ->with(['solicitacao.atribuicoes.motorista:id,name'])
            ->latest('registrado_em')
            ->limit(6)
            ->get();

        $graficos = $this->montarGraficos($viagensQuery);
        $timeline = $this->montarTimeline($operadorId);
        $clientes = Cliente::query()->where('operador_id', $operadorId)->where('ativo', true)->orderBy('nome_fantasia')->get(['id', 'nome_fantasia', 'razao_social']);
        $motoristas = (clone $motoristasAtivos)->orderBy('name')->get(['id', 'name']);
        $statusOptions = ViagemStatus::labels();
        $periodoLabel = match ($request->input('periodo', 'hoje')) {
            '7_dias' => 'Próximos 7 dias',
            '30_dias' => 'Próximos 30 dias',
            'todos' => 'Todo o período',
            default => 'Hoje',
        };

        return view('painel.dashboard', compact(
            'metricas', 'alertas', 'indicadores', 'ultimasSolicitacoes', 'ocorrenciasRecentes',
            'graficos', 'timeline', 'clientes', 'motoristas', 'statusOptions', 'periodoLabel', 'tendenciaViagens'
        ));
    }

    private function viagensFiltradas(Request $request, int $operadorId, bool $comPeriodo = true): Builder
    {
        $query = SolicitacaoViagem::query()->where('operador_id', $operadorId);

        if ($request->filled('cliente_id')) {
            $query->where('cliente_id', $request->integer('cliente_id'));
        }
        if ($request->filled('motorista_id')) {
            $motoristaId = $request->integer('motorista_id');
            $query->whereHas('atribuicoes', fn (Builder $subquery) => $subquery->where('motorista_id', $motoristaId));
        }
        if ($request->filled('situacao')) {
            $query->where('status', $request->string('situacao'));
        }

        if ($comPeriodo) {
            match ($request->input('periodo', 'hoje')) {
                '7_dias' => $query->whereBetween('data_hora', [today(), today()->copy()->addDays(7)->endOfDay()]),
                '30_dias' => $query->whereBetween('data_hora', [today(), today()->copy()->addDays(30)->endOfDay()]),
                'todos' => null,
                default => $query->whereDate('data_hora', today()),
            };
        }

        return $query;
    }

    private function montarAlertas(array $metricas, int $cnhVencidas, int $cnhProximas, $manutencoes, $veiculos, int $ocorrencias): array
    {
        $alertas = [];
        if ($metricas['atrasadas'] > 0) {
            $alertas[] = ['nivel' => 'danger', 'icone' => 'bi-clock-history', 'titulo' => "{$metricas['atrasadas']} viagem(ns) atrasada(s)", 'texto' => 'Exigem acompanhamento imediato da operação.', 'url' => route('painel.operador.solicitacoes.index', ['status' => ViagemStatus::ATRASADA])];
        }
        if ($metricas['veiculos_bloqueados'] > 0) {
            $alertas[] = ['nivel' => 'danger', 'icone' => 'bi-truck-flatbed', 'titulo' => "{$metricas['veiculos_bloqueados']} veículo(s) bloqueado(s)", 'texto' => 'Indisponíveis para novas atribuições.', 'url' => route('master.veiculos.index')];
        }
        if ($metricas['checklists_pendentes'] > 0) {
            $alertas[] = ['nivel' => 'warning', 'icone' => 'bi-clipboard2-pulse', 'titulo' => "{$metricas['checklists_pendentes']} checklist(s) pendente(s)", 'texto' => 'Aguardando conclusão operacional.', 'url' => route('painel.operador.checklists.index')];
        }
        if ($cnhVencidas > 0 || $cnhProximas > 0) {
            $alertas[] = ['nivel' => $cnhVencidas > 0 ? 'danger' : 'warning', 'icone' => 'bi-person-vcard', 'titulo' => $cnhVencidas > 0 ? "{$cnhVencidas} CNH(s) vencida(s)" : "{$cnhProximas} CNH(s) vence(m) em até 30 dias", 'texto' => 'Revise os documentos dos motoristas.', 'url' => route('master.motoristas.index')];
        }

        $manutencaoCritica = $manutencoes->sortBy('km_restante')->first(fn ($item) => in_array($item->status_dashboard, ['vencido', 'proximo_vencimento'], true));
        if ($manutencaoCritica) {
            $veiculo = $veiculos->firstWhere('id', $manutencaoCritica->veiculo_id);
            $textoKm = $manutencaoCritica->km_restante <= 0 ? 'Manutenção vencida por KM.' : "Vence em {$manutencaoCritica->km_restante} km.";
            $alertas[] = ['nivel' => $manutencaoCritica->status_dashboard === 'vencido' ? 'danger' : 'warning', 'icone' => 'bi-wrench-adjustable-circle', 'titulo' => "{$veiculo?->placa}: {$manutencaoCritica->item}", 'texto' => $textoKm, 'url' => route('master.veiculos.show', $manutencaoCritica->veiculo_id)];
        }
        if ($ocorrencias > 0) {
            $alertas[] = ['nivel' => 'warning', 'icone' => 'bi-exclamation-diamond', 'titulo' => "{$ocorrencias} ocorrência(s) nas últimas 24h", 'texto' => 'Confira os registros mais recentes.', 'url' => route('painel.operador.solicitacoes.index')];
        }

        if ($alertas === []) {
            $alertas[] = ['nivel' => 'success', 'icone' => 'bi-shield-check', 'titulo' => 'Operação sem alertas críticos', 'texto' => 'Nenhuma pendência imediata foi identificada.', 'url' => route('painel.operador.solicitacoes.index')];
        }

        return $alertas;
    }

    private function montarGraficos(Builder $viagensQuery): array
    {
        $viagens = (clone $viagensQuery)->with('cliente:id,nome_fantasia,razao_social')->get();
        $porHora = array_fill(0, 24, 0);
        foreach ($viagens as $viagem) {
            $porHora[(int) $viagem->data_hora->format('G')]++;
        }

        $porCliente = $viagens->groupBy('cliente_id')->map(fn ($grupo) => [
            'nome' => $grupo->first()->cliente?->nome_fantasia ?: $grupo->first()->cliente?->razao_social ?: 'Sem cliente',
            'total' => $grupo->count(),
        ])->sortByDesc('total')->take(7)->values();

        $statusPresentes = collect(ViagemStatus::labels())->keys()->filter(fn ($status) => $viagens->contains('status', $status));

        return [
            'horas' => ['labels' => collect(range(0, 23))->map(fn ($hora) => sprintf('%02dh', $hora))->all(), 'data' => array_values($porHora)],
            'naturezas' => ['programadas' => $viagens->where('natureza', 'programada')->count(), 'extras' => $viagens->where('natureza', 'extra')->count()],
            'clientes' => ['labels' => $porCliente->pluck('nome')->all(), 'data' => $porCliente->pluck('total')->all()],
            'status' => [
                'labels' => $statusPresentes->map(fn ($status) => ViagemStatus::label($status))->values()->all(),
                'data' => $statusPresentes->map(fn ($status) => $viagens->where('status', $status)->count())->values()->all(),
            ],
        ];
    }

    private function montarTimeline(int $operadorId)
    {
        $eventos = collect();

        SolicitacaoViagem::query()->where('operador_id', $operadorId)->latest()->limit(6)->get()->each(function ($viagem) use ($eventos) {
            $eventos->push(['data' => $viagem->created_at, 'icone' => 'bi-plus-circle', 'titulo' => "Viagem #{$viagem->id} criada", 'texto' => "{$viagem->origem} → {$viagem->destino}", 'url' => route('painel.operador.solicitacoes.show', $viagem->id)]);
            if ($viagem->status === ViagemStatus::FINALIZADA) {
                $eventos->push(['data' => $viagem->updated_at, 'icone' => 'bi-check2-circle', 'titulo' => "Viagem #{$viagem->id} finalizada", 'texto' => 'Operação concluída', 'url' => route('painel.operador.solicitacoes.show', $viagem->id)]);
            }
        });
        Checklist::query()->where('operador_id', $operadorId)->whereNotNull('finished_at')->latest('finished_at')->limit(5)->get()->each(fn ($checklist) => $eventos->push(['data' => $checklist->finished_at, 'icone' => 'bi-clipboard-check', 'titulo' => 'Checklist concluído', 'texto' => $checklist->placa ?: 'Veículo não identificado', 'url' => route('checklists.show', $checklist)]));
        OcorrenciaViagem::query()->where('operador_id', $operadorId)->latest('registrado_em')->limit(5)->get()->each(fn ($ocorrencia) => $eventos->push(['data' => $ocorrencia->registrado_em ?: $ocorrencia->created_at, 'icone' => 'bi-exclamation-triangle', 'titulo' => 'Ocorrência registrada', 'texto' => $ocorrencia->tipo, 'url' => route('painel.operador.solicitacoes.show', $ocorrencia->solicitacao_id)]));

        return $eventos->filter(fn ($evento) => $evento['data'])->sortByDesc('data')->take(10)->values();
    }
}
