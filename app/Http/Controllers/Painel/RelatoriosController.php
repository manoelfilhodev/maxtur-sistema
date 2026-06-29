<?php

namespace App\Http\Controllers\Painel;

use App\Http\Controllers\Controller;
use App\Models\AtrasoPassageiro;
use App\Models\AtrasoViagem;
use App\Models\Cliente;
use App\Models\OcorrenciaViagem;
use App\Models\SolicitacaoAtribuicao;
use App\Models\SolicitacaoViagem;
use App\Models\User;
use App\Models\Veiculo;
use App\Services\TenantContext;
use App\Support\ViagemStatus;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class RelatoriosController extends Controller
{
    public function __construct(private TenantContext $tenantContext) {}

    public function index(Request $request)
    {
        $this->validarFiltros($request);
        $operadorId = $this->tenantContext->operadorId($request->user());
        $query = $this->queryViagens($request, $operadorId);
        $totais = $this->calcularTotais(clone $query);

        $viagens = $this->comDetalhes(clone $query)
            ->orderByDesc('data_hora')
            ->paginate(30)
            ->withQueryString();

        return view('painel.relatorios.index', [
            'viagens' => $viagens,
            'totais' => $totais,
            'clientes' => Cliente::query()->where('operador_id', $operadorId)->orderBy('razao_social')->get(['id', 'razao_social', 'nome_fantasia']),
            'motoristas' => User::query()->where('operador_id', $operadorId)->where(function (Builder $query) {
                $query->whereIn('role', ['motorista', 'MOTORISTA'])->orWhereIn('cargo', ['motorista', 'MOTORISTA']);
            })->orderBy('name')->get(['id', 'name']),
            'veiculos' => Veiculo::query()->where('operador_id', $operadorId)->orderBy('placa')->get(['id', 'placa', 'modelo']),
            'statusOptions' => ViagemStatus::labels(),
            'pdfUrl' => route('painel.relatorios.viagens.pdf', $this->parametrosPdf($request)),
        ]);
    }

    public function viagensPdf(Request $request): Response
    {
        $this->prepararFiltrosPdf($request);
        $this->validarFiltros($request);
        $operadorId = $this->tenantContext->operadorId($request->user());
        $query = $this->queryViagens($request, $operadorId);
        $totais = $this->calcularTotais(clone $query);
        $viagens = $this->comDetalhes(clone $query)->orderBy('data_hora')->get();
        $clienteRestritoId = $this->clienteRestritoId($request->user());
        $cliente = $clienteRestritoId
            ? Cliente::query()->find($clienteRestritoId)
            : ($request->filled('cliente_id') ? Cliente::query()->where('operador_id', $operadorId)->find($request->integer('cliente_id')) : null);
        $geradoEm = now();

        $dados = [
            'titulo' => 'Relatório de Viagens para Validação do Cliente',
            'clienteLabel' => $cliente?->nome_fantasia ?: $cliente?->razao_social ?: 'Todos os clientes',
            'periodoLabel' => $request->date('data_inicio')->format('d/m/Y').' a '.$request->date('data_fim')->format('d/m/Y'),
            'geradoEm' => $geradoEm,
            'filtros' => $this->descreverFiltros($request, $operadorId),
            'totais' => $totais,
            'viagens' => $viagens,
            'totalizadores' => $this->totalizadoresPdf($viagens),
            'eventos' => $this->eventosPdf($viagens),
            'logoDataUri' => $this->logoDataUri(),
        ];

        $nomeCliente = Str::slug($dados['clienteLabel']) ?: 'clientes';
        $arquivo = "validacao-viagens-{$nomeCliente}-".$geradoEm->format('Y-m-d-His').'.pdf';

        return Pdf::loadView('painel.relatorios.viagens-pdf', $dados)
            ->setPaper('a4', 'landscape')
            ->setOption(['defaultFont' => 'DejaVu Sans', 'isRemoteEnabled' => false, 'isPhpEnabled' => true])
            ->download($arquivo);
    }

    public function viagensCsv(Request $request): StreamedResponse
    {
        $this->validarFiltros($request);
        $operadorId = $this->tenantContext->operadorId($request->user());
        $query = $this->queryViagens($request, $operadorId);
        $totais = $this->calcularTotais(clone $query);
        $filtros = $this->descreverFiltros($request, $operadorId);
        $arquivo = 'relatorio-viagens-'.now()->format('Y-m-d-His').'.csv';

        return response()->streamDownload(function () use ($query, $totais, $filtros) {
            $handle = fopen('php://output', 'wb');
            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, ['RELATÓRIO GERENCIAL DE VIAGENS'], ';');
            fputcsv($handle, ['Gerado em', now()->format('d/m/Y H:i:s')], ';');
            fputcsv($handle, ['Filtros', implode(' | ', $filtros)], ';');
            fputcsv($handle, [], ';');
            fputcsv($handle, ['TOTALIZADORES'], ';');
            foreach ($this->rotulosTotais() as $chave => $rotulo) {
                fputcsv($handle, [$rotulo, $totais[$chave]], ';');
            }
            fputcsv($handle, [], ';');
            fputcsv($handle, [
                'ID', 'Prevista em', 'Cliente', 'Origem', 'Destino', 'Motorista', 'Veículo',
                'Natureza', 'Tipo/período', 'Status', 'Checklist', 'Atraso total (min)',
                'Atraso de viagem', 'Atraso de passageiro', 'Ocorrências', 'Criado em',
            ], ';');

            $this->comDetalhes(clone $query)->orderBy('data_hora')->chunk(300, function ($viagens) use ($handle) {
                foreach ($viagens as $viagem) {
                    $atribuicao = $viagem->ultimaAtribuicao;
                    $atrasoTotal = (int) $viagem->atraso_viagem_total + (int) $viagem->atraso_passageiro_total;
                    fputcsv($handle, [
                        $viagem->id,
                        $viagem->data_hora?->format('d/m/Y H:i'),
                        $viagem->cliente?->nome_fantasia ?: $viagem->cliente?->razao_social ?: 'Não informado',
                        $viagem->origem,
                        $viagem->destino,
                        $atribuicao?->motorista?->name ?: 'Não informado',
                        $atribuicao?->veiculo?->placa ?: 'Não informado',
                        $viagem->natureza === 'extra' ? 'Extra' : 'Programada',
                        $this->tipoPeriodoLabel($viagem->tipo_periodo),
                        $viagem->statusLabel(),
                        $viagem->ultimoChecklist?->status ?: 'Não iniciado',
                        $atrasoTotal,
                        $viagem->atrasos_viagem_count > 0 ? 'Sim' : 'Não',
                        $viagem->atrasos_passageiro_count > 0 ? 'Sim' : 'Não',
                        $viagem->ocorrencias_count,
                        $viagem->created_at?->format('d/m/Y H:i'),
                    ], ';');
                }
            });
            fclose($handle);
        }, $arquivo, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function motoristasIndex(Request $request)
    {
        $this->prepararFiltrosMotorista($request);
        $this->validarFiltros($request);
        $operadorId = $this->tenantContext->operadorId($request->user());
        $query = $this->queryViagens($request, $operadorId)->whereHas('ultimaAtribuicao');
        $motorista = $request->filled('motorista_id')
            ? $this->motoristaDoOperador($operadorId, $request->integer('motorista_id'))
            : null;

        return view('painel.relatorios.motoristas.index', [
            'viagens' => $this->comDetalhes(clone $query)->orderByDesc('data_hora')->paginate(30)->withQueryString(),
            'totais' => $this->calcularValidacaoMotorista(clone $query),
            'motorista' => $motorista,
            'motoristas' => $this->motoristasDoOperador($operadorId),
            'clientes' => Cliente::query()->where('operador_id', $operadorId)->orderBy('razao_social')->get(['id', 'razao_social', 'nome_fantasia']),
            'statusOptions' => ViagemStatus::labels(),
            'pdfUrl' => route('painel.relatorios.motoristas.pdf', $request->query()),
        ]);
    }

    public function motoristasCsv(Request $request): StreamedResponse
    {
        $this->prepararFiltrosMotorista($request);
        $this->validarFiltros($request);
        $operadorId = $this->tenantContext->operadorId($request->user());
        $query = $this->queryViagens($request, $operadorId)->whereHas('ultimaAtribuicao');
        $totais = $this->calcularValidacaoMotorista(clone $query);
        $arquivo = 'validacao-motoristas-'.now()->format('Y-m-d-His').'.csv';

        return response()->streamDownload(function () use ($query, $totais) {
            $handle = fopen('php://output', 'wb');
            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, ['RELATÓRIO DE VALIDAÇÃO DO MOTORISTA'], ';');
            fputcsv($handle, ['Gerado em', now()->format('d/m/Y H:i:s')], ';');
            fputcsv($handle, ['Período', request('data_inicio').' a '.request('data_fim')], ';');
            fputcsv($handle, ['Total de viagens', $totais['viagens']], ';');
            fputcsv($handle, ['Viagens finalizadas', $totais['finalizadas']], ';');
            fputcsv($handle, ['Valor calculado por viagem', number_format($totais['valor_calculado'], 2, ',', '.')], ';');
            fputcsv($handle, [], ';');
            fputcsv($handle, ['Viagem', 'Data/hora', 'Motorista', 'Cliente', 'Trajeto', 'Veículo', 'Natureza', 'Status', 'Atraso (min)', 'Ocorrências', 'Regra', 'Valor unitário', 'Valor calculado'], ';');

            $this->comDetalhes(clone $query)->orderBy('data_hora')->chunk(300, function ($viagens) use ($handle) {
                foreach ($viagens as $viagem) {
                    $motorista = $viagem->ultimaAtribuicao?->motorista;
                    $elegivel = $viagem->status === ViagemStatus::FINALIZADA && $motorista?->tipo_recebimento === 'por_viagem';
                    $valor = $elegivel ? (float) $motorista->valor_por_viagem : 0;
                    fputcsv($handle, [
                        '#'.$viagem->id, $viagem->data_hora?->format('d/m/Y H:i'), $motorista?->name ?: 'Não informado',
                        $viagem->cliente?->nome_fantasia ?: $viagem->cliente?->razao_social ?: 'Não informado',
                        $viagem->origem.' → '.$viagem->destino, $viagem->ultimaAtribuicao?->veiculo?->placa ?: 'Não informado',
                        $viagem->natureza === 'extra' ? 'Extra' : 'Programada', $viagem->statusLabel(),
                        (int) $viagem->atraso_viagem_total + (int) $viagem->atraso_passageiro_total,
                        $viagem->ocorrencias_count,
                        $motorista?->tipo_recebimento === 'por_viagem' ? 'Por viagem' : 'Salário',
                        $motorista?->tipo_recebimento === 'por_viagem' ? number_format((float) $motorista->valor_por_viagem, 2, ',', '.') : '',
                        number_format($valor, 2, ',', '.'),
                    ], ';');
                }
            });
            fclose($handle);
        }, $arquivo, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function motoristasPdf(Request $request): Response
    {
        $this->prepararFiltrosMotorista($request);
        $this->validarFiltros($request);
        $operadorId = $this->tenantContext->operadorId($request->user());
        $motorista = $request->filled('motorista_id')
            ? $this->motoristaDoOperador($operadorId, $request->integer('motorista_id'))
            : null;
        abort_if($request->filled('motorista_id') && ! $motorista, 404);

        $query = $this->queryViagens($request, $operadorId)->whereHas('ultimaAtribuicao');
        $viagens = $this->comDetalhes(clone $query)->orderBy('data_hora')->get();
        $totais = $this->calcularValidacaoMotorista(clone $query);
        $identificacao = $motorista ? Str::slug($motorista->name) : 'todos-os-motoristas';
        $arquivo = 'validacao-motorista-'.$identificacao.'-'.now()->format('Y-m-d-His').'.pdf';

        return Pdf::loadView('painel.relatorios.motoristas.pdf', [
            'motorista' => $motorista,
            'viagens' => $viagens,
            'totais' => $totais,
            'periodoLabel' => $request->date('data_inicio')->format('d/m/Y').' a '.$request->date('data_fim')->format('d/m/Y'),
            'geradoEm' => now(),
            'logoDataUri' => $this->logoDataUri(),
        ])->setPaper('a4', 'landscape')
            ->setOption(['defaultFont' => 'DejaVu Sans', 'isRemoteEnabled' => false, 'isPhpEnabled' => true])
            ->download($arquivo);
    }

    private function queryViagens(Request $request, int $operadorId): Builder
    {
        $query = SolicitacaoViagem::query()->where('operador_id', $operadorId);

        if ($request->filled('data_inicio')) {
            $query->whereDate('data_hora', '>=', $request->date('data_inicio'));
        }
        if ($request->filled('data_fim')) {
            $query->whereDate('data_hora', '<=', $request->date('data_fim'));
        }
        if ($clienteId = $this->clienteRestritoId($request->user())) {
            $query->where('cliente_id', $clienteId);
        } elseif ($request->filled('cliente_id')) {
            $query->where('cliente_id', $request->integer('cliente_id'));
        }
        if ($request->filled('motorista_id')) {
            $query->whereHas('ultimaAtribuicao', fn (Builder $subquery) => $subquery->where('motorista_id', $request->integer('motorista_id')));
        }
        if ($request->filled('veiculo_id')) {
            $query->whereHas('ultimaAtribuicao', fn (Builder $subquery) => $subquery->where('veiculo_id', $request->integer('veiculo_id')));
        }
        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }
        if ($request->filled('natureza')) {
            $query->where('natureza', $request->string('natureza'));
        }
        if ($request->filled('tipo_periodo')) {
            $query->where('tipo_periodo', $request->string('tipo_periodo'));
        }

        return $query;
    }

    private function comDetalhes(Builder $query): Builder
    {
        return $query
            ->with([
                'cliente:id,nome_fantasia,razao_social',
                'ultimaAtribuicao' => fn ($subquery) => $subquery->select([
                    'solicitacao_atribuicoes.id', 'solicitacao_atribuicoes.solicitacao_id',
                    'solicitacao_atribuicoes.motorista_id', 'solicitacao_atribuicoes.veiculo_id',
                ]),
                'ultimaAtribuicao.motorista:id,name,cpf,tipo_recebimento,valor_salario,valor_por_viagem',
                'ultimaAtribuicao.veiculo:id,placa,modelo',
                'ultimoChecklist' => fn ($subquery) => $subquery->select([
                    'checklists.id', 'checklists.solicitacao_id', 'checklists.status',
                    'checklists.resultado', 'checklists.finished_at',
                ]),
                'atrasosViagem:id,solicitacao_id,minutos_atraso,motivo,ocorrido_em,created_at',
                'atrasosPassageiro:id,solicitacao_id,passageiro_id,minutos_atraso,motivo,ocorrido_em,created_at',
                'atrasosPassageiro.passageiro:id,nome',
                'ocorrencias:id,solicitacao_id,tipo,descricao,ocorrido_em,registrado_por,registrado_em,created_at',
                'ocorrencias.responsavel:id,name',
            ])
            ->withSum('atrasosViagem as atraso_viagem_total', 'minutos_atraso')
            ->withSum('atrasosPassageiro as atraso_passageiro_total', 'minutos_atraso')
            ->withCount(['atrasosViagem', 'atrasosPassageiro', 'ocorrencias']);
    }

    private function calcularTotais(Builder $query): array
    {
        $ids = (clone $query)->select('solicitacoes_viagem.id');
        $atrasosViagem = AtrasoViagem::query()->whereIn('solicitacao_id', clone $ids);
        $atrasosPassageiro = AtrasoPassageiro::query()->whereIn('solicitacao_id', clone $ids);
        $minutosAtraso = (int) (clone $atrasosViagem)->sum('minutos_atraso') + (int) (clone $atrasosPassageiro)->sum('minutos_atraso');
        $atrasosRegistrados = (clone $atrasosViagem)->count() + (clone $atrasosPassageiro)->count();
        $viagensComAtraso = DB::query()->fromSub(
            (clone $atrasosViagem)->select('solicitacao_id')->union((clone $atrasosPassageiro)->select('solicitacao_id')),
            'viagens_atrasadas'
        )->distinct()->count('solicitacao_id');

        $atribuicoes = SolicitacaoAtribuicao::query()->whereIn('solicitacao_id', clone $ids);
        $topCliente = (clone $query)->selectRaw('cliente_id, COUNT(*) total')->groupBy('cliente_id')->orderByDesc('total')->first();
        $topMotorista = (clone $atribuicoes)->selectRaw('motorista_id, COUNT(DISTINCT solicitacao_id) total')->groupBy('motorista_id')->orderByDesc('total')->first();
        $clienteDestaque = $topCliente ? Cliente::query()->find($topCliente->cliente_id) : null;

        return [
            'total' => (clone $query)->count(),
            'programadas' => (clone $query)->where('natureza', 'programada')->count(),
            'extras' => (clone $query)->where('natureza', 'extra')->count(),
            'finalizadas' => (clone $query)->where('status', ViagemStatus::FINALIZADA)->count(),
            'canceladas' => (clone $query)->where('status', ViagemStatus::CANCELADA)->count(),
            'atrasadas' => $viagensComAtraso,
            'atrasos_registrados' => $atrasosRegistrados,
            'minutos_atraso' => $minutosAtraso,
            'media_atraso' => $viagensComAtraso > 0 ? round($minutosAtraso / $viagensComAtraso, 1) : 0,
            'ocorrencias' => OcorrenciaViagem::query()->whereIn('solicitacao_id', clone $ids)->count(),
            'motoristas' => (clone $atribuicoes)->distinct()->count('motorista_id'),
            'veiculos' => (clone $atribuicoes)->distinct()->count('veiculo_id'),
            'top_cliente' => $clienteDestaque?->nome_fantasia ?: $clienteDestaque?->razao_social,
            'top_motorista' => $topMotorista ? User::query()->find($topMotorista->motorista_id)?->name : null,
        ];
    }

    private function calcularValidacaoMotorista(Builder $query): array
    {
        $ids = (clone $query)->select('solicitacoes_viagem.id');
        $atrasosViagem = AtrasoViagem::query()->whereIn('solicitacao_id', clone $ids);
        $atrasosPassageiro = AtrasoPassageiro::query()->whereIn('solicitacao_id', clone $ids);
        $finalizadasIds = (clone $query)->where('status', ViagemStatus::FINALIZADA)->select('solicitacoes_viagem.id');

        $valorCalculado = DB::table('solicitacao_atribuicoes as atribuicao')
            ->join('users as motorista', 'motorista.id', '=', 'atribuicao.motorista_id')
            ->whereIn('atribuicao.solicitacao_id', clone $finalizadasIds)
            ->where('motorista.tipo_recebimento', 'por_viagem')
            ->whereNotExists(function ($subquery) {
                $subquery->selectRaw('1')->from('solicitacao_atribuicoes as posterior')
                    ->whereColumn('posterior.solicitacao_id', 'atribuicao.solicitacao_id')
                    ->whereColumn('posterior.id', '>', 'atribuicao.id');
            })
            ->sum('motorista.valor_por_viagem');

        return [
            'viagens' => (clone $query)->count(),
            'finalizadas' => (clone $query)->where('status', ViagemStatus::FINALIZADA)->count(),
            'extras' => (clone $query)->where('natureza', 'extra')->count(),
            'canceladas' => (clone $query)->where('status', ViagemStatus::CANCELADA)->count(),
            'minutos_atraso' => (int) (clone $atrasosViagem)->sum('minutos_atraso') + (int) (clone $atrasosPassageiro)->sum('minutos_atraso'),
            'ocorrencias' => OcorrenciaViagem::query()->whereIn('solicitacao_id', clone $ids)->count(),
            'valor_calculado' => (float) $valorCalculado,
        ];
    }

    private function validarFiltros(Request $request): void
    {
        $request->validate([
            'data_inicio' => ['nullable', 'date'],
            'data_fim' => ['nullable', 'date', 'after_or_equal:data_inicio'],
            'cliente_id' => ['nullable', 'integer', 'exists:clientes,id'],
            'motorista_id' => ['nullable', 'integer', 'exists:users,id'],
            'veiculo_id' => ['nullable', 'integer', 'exists:veiculos,id'],
            'status' => ['nullable', 'in:'.implode(',', ViagemStatus::all())],
            'natureza' => ['nullable', 'in:programada,extra'],
            'tipo_periodo' => ['nullable', 'in:diario,mensal,esporadico'],
        ]);
    }

    private function descreverFiltros(Request $request, int $operadorId): array
    {
        $filtros = [
            'Período: '.($request->filled('data_inicio') ? $request->date('data_inicio')->format('d/m/Y') : 'início').
                ' a '.($request->filled('data_fim') ? $request->date('data_fim')->format('d/m/Y') : 'sem limite final'),
        ];
        if ($clienteId = $this->clienteRestritoId($request->user())) {
            $cliente = Cliente::query()->where('operador_id', $operadorId)->find($clienteId);
            $filtros[] = 'Cliente: '.($cliente?->nome_fantasia ?: $cliente?->razao_social ?: 'Não informado');
        } elseif ($request->filled('cliente_id')) {
            $filtros[] = 'Cliente: '.Cliente::query()->where('operador_id', $operadorId)->find($request->integer('cliente_id'))?->razao_social;
        }
        if ($request->filled('motorista_id')) {
            $filtros[] = 'Motorista: '.User::query()->where('operador_id', $operadorId)->find($request->integer('motorista_id'))?->name;
        }
        if ($request->filled('veiculo_id')) {
            $filtros[] = 'Veículo: '.Veiculo::query()->where('operador_id', $operadorId)->find($request->integer('veiculo_id'))?->placa;
        }
        if ($request->filled('status')) {
            $filtros[] = 'Status: '.ViagemStatus::label((string) $request->string('status'));
        }
        if ($request->filled('natureza')) {
            $filtros[] = 'Natureza: '.ucfirst((string) $request->string('natureza'));
        }
        if ($request->filled('tipo_periodo')) {
            $filtros[] = 'Tipo/período: '.$this->tipoPeriodoLabel((string) $request->string('tipo_periodo'));
        }

        return $filtros;
    }

    private function tipoPeriodoLabel(?string $tipo): string
    {
        return ['diario' => 'Diário', 'mensal' => 'Mensal', 'esporadico' => 'Esporádico'][$tipo] ?? 'Não informado';
    }

    private function rotulosTotais(): array
    {
        return [
            'total' => 'Total de viagens', 'programadas' => 'Programadas', 'extras' => 'Extras',
            'finalizadas' => 'Finalizadas', 'canceladas' => 'Canceladas', 'atrasadas' => 'Viagens com atraso',
            'atrasos_registrados' => 'Atrasos registrados',
            'minutos_atraso' => 'Minutos de atraso', 'media_atraso' => 'Média de atraso por viagem',
            'ocorrencias' => 'Ocorrências', 'motoristas' => 'Motoristas utilizados', 'veiculos' => 'Veículos utilizados',
        ];
    }

    private function prepararFiltrosPdf(Request $request): void
    {
        $request->merge([
            'data_inicio' => $request->input('data_inicio') ?: today()->startOfMonth()->toDateString(),
            'data_fim' => $request->input('data_fim') ?: today()->toDateString(),
            'status' => $request->input('status') ?: ViagemStatus::FINALIZADA,
        ]);
    }

    private function prepararFiltrosMotorista(Request $request): void
    {
        $request->merge([
            'data_inicio' => $request->input('data_inicio') ?: today()->startOfMonth()->toDateString(),
            'data_fim' => $request->input('data_fim') ?: today()->toDateString(),
            'status' => $request->has('status') ? $request->input('status') : ViagemStatus::FINALIZADA,
        ]);
    }

    private function motoristasDoOperador(int $operadorId)
    {
        return User::query()->where('operador_id', $operadorId)->where(function (Builder $query) {
            $query->whereIn('role', ['motorista', 'MOTORISTA'])->orWhereIn('cargo', ['motorista', 'MOTORISTA']);
        })->orderBy('name')->get(['id', 'name', 'cpf', 'tipo_recebimento', 'valor_salario', 'valor_por_viagem']);
    }

    private function motoristaDoOperador(int $operadorId, int $motoristaId): ?User
    {
        return $this->motoristasDoOperador($operadorId)->firstWhere('id', $motoristaId);
    }

    private function parametrosPdf(Request $request): array
    {
        return array_filter([
            ...$request->only(['cliente_id', 'data_inicio', 'data_fim', 'natureza', 'tipo_periodo', 'status', 'motorista_id', 'veiculo_id']),
            'data_inicio' => $request->input('data_inicio') ?: today()->startOfMonth()->toDateString(),
            'data_fim' => $request->input('data_fim') ?: today()->toDateString(),
            'status' => $request->input('status') ?: ViagemStatus::FINALIZADA,
        ], fn ($value) => $value !== null && $value !== '');
    }

    private function totalizadoresPdf($viagens): array
    {
        $porMotorista = $viagens->groupBy(fn ($viagem) => $viagem->ultimaAtribuicao?->motorista?->name ?: 'Não informado')
            ->map(fn ($grupo, $nome) => $this->totalizadorRecurso($nome, $grupo))->sortByDesc('viagens')->values();
        $porVeiculo = $viagens->groupBy(fn ($viagem) => $viagem->ultimaAtribuicao?->veiculo?->placa ?: 'Não informado')
            ->map(fn ($grupo, $nome) => $this->totalizadorRecurso($nome, $grupo))->sortByDesc('viagens')->values();

        return [
            'natureza' => ['Programadas' => $viagens->where('natureza', 'programada')->count(), 'Extras' => $viagens->where('natureza', 'extra')->count()],
            'tipo' => [
                'Diárias' => $viagens->where('tipo_periodo', 'diario')->count(),
                'Mensais' => $viagens->where('tipo_periodo', 'mensal')->count(),
                'Esporádicas' => $viagens->where('tipo_periodo', 'esporadico')->count(),
            ],
            'status' => collect(ViagemStatus::labels())->mapWithKeys(fn ($label, $status) => [$label => $viagens->where('status', $status)->count()])->filter()->all(),
            'clientes' => $viagens->groupBy(fn ($viagem) => $viagem->cliente?->nome_fantasia ?: $viagem->cliente?->razao_social ?: 'Não informado')->map->count()->sortDesc(),
            'motoristas' => $porMotorista,
            'veiculos' => $porVeiculo,
            'trajetos' => $viagens->groupBy(fn ($viagem) => $viagem->origem.' → '.$viagem->destino)->map->count()->sortDesc(),
        ];
    }

    private function totalizadorRecurso(string $nome, $viagens): array
    {
        return [
            'nome' => $nome,
            'viagens' => $viagens->count(),
            'atrasos' => $viagens->sum(fn ($viagem) => (int) $viagem->atrasos_viagem_count + (int) $viagem->atrasos_passageiro_count),
            'minutos' => $viagens->sum(fn ($viagem) => (int) $viagem->atraso_viagem_total + (int) $viagem->atraso_passageiro_total),
        ];
    }

    private function eventosPdf($viagens)
    {
        return $viagens->flatMap(function ($viagem) {
            $motorista = $viagem->ultimaAtribuicao?->motorista?->name ?: 'Não informado';
            $eventos = collect();
            foreach ($viagem->atrasosViagem as $atraso) {
                $eventos->push([
                    'viagem_id' => $viagem->id, 'ocorrido_em' => $atraso->ocorrido_em ?: $atraso->created_at,
                    'tipo' => 'Atraso de viagem', 'motorista' => $motorista, 'passageiro' => null,
                    'minutos' => $atraso->minutos_atraso, 'descricao' => $atraso->motivo ?: 'Sem motivo informado',
                    'registrado_em' => $atraso->created_at,
                ]);
            }
            foreach ($viagem->atrasosPassageiro as $atraso) {
                $eventos->push([
                    'viagem_id' => $viagem->id, 'ocorrido_em' => $atraso->ocorrido_em ?: $atraso->created_at,
                    'tipo' => 'Atraso de passageiro', 'motorista' => $motorista,
                    'passageiro' => $atraso->passageiro?->nome ?: 'Não informado',
                    'minutos' => $atraso->minutos_atraso, 'descricao' => $atraso->motivo ?: 'Sem motivo informado',
                    'registrado_em' => $atraso->created_at,
                ]);
            }
            foreach ($viagem->ocorrencias as $ocorrencia) {
                $eventos->push([
                    'viagem_id' => $viagem->id, 'ocorrido_em' => $ocorrencia->ocorrido_em ?: $ocorrencia->registrado_em ?: $ocorrencia->created_at,
                    'tipo' => 'Ocorrência operacional', 'motorista' => $motorista, 'passageiro' => null,
                    'minutos' => null, 'descricao' => $ocorrencia->tipo.' — '.$ocorrencia->descricao,
                    'registrado_em' => $ocorrencia->created_at,
                ]);
            }

            return $eventos;
        })->sortBy('ocorrido_em')->values();
    }

    private function logoDataUri(): ?string
    {
        $path = public_path('images/logo.png');
        if (! is_file($path)) {
            return null;
        }

        return 'data:image/png;base64,'.base64_encode((string) file_get_contents($path));
    }

    private function clienteRestritoId(User $user): ?int
    {
        if ($user->cliente_id && ! $user->isAdmin() && ! $user->isOperador() && ! $user->isMaster()) {
            return (int) $user->cliente_id;
        }

        return null;
    }

    public function batidasIndex()
    {
        $usuarios = DB::table('users')->select('id', 'name', 'cpf')->orderBy('name')->get();

        return view('painel.relatorios.batidas.index', compact('usuarios'));
    }

    public function batidasExcel(Request $request)
    {
        return back()->with('error', 'Exportacao Excel indisponivel neste momento.');
    }

    public function batidasPdf(Request $request)
    {
        return back()->with('error', 'Exportacao PDF indisponivel neste momento.');
    }

    public function diarioIndex()
    {
        $usuarios = DB::table('users')->select('id', 'name', 'cpf')->orderBy('name')->get();

        return view('painel.relatorios.diario.index', compact('usuarios'));
    }

    public function diarioExcel(Request $request)
    {
        return back()->with('error', 'Exportacao Excel indisponivel neste momento.');
    }

    public function diarioPdf(Request $request)
    {
        return back()->with('error', 'Exportacao PDF indisponivel neste momento.');
    }
}
