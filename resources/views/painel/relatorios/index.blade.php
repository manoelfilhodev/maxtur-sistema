@extends('layouts.app')

@section('page-heading')
    @include('partials.panel.page-header', [
        'title' => 'Relatório de viagens',
        'subtitle' => 'Conferência operacional, atrasos, ocorrências e recursos utilizados',
    ])
@endsection

@section('content')
<div class="sx-container travel-report">
    @include('partials.panel.flash-messages')
    @include('partials.panel.form-errors')

    <div class="sx-card mb-3">
        <div class="sx-card-header">
            <div><h5 class="sx-card-title">Filtros do relatório</h5><div class="sx-muted small">Combine os filtros para localizar exatamente as viagens que deseja conferir.</div></div>
            <div class="d-flex gap-2 flex-wrap">
                <a href="{{ route('painel.relatorios.motoristas.index') }}" class="btn btn-outline-light btn-sm"><i class="bi bi-person-check"></i> Validar motorista</a>
                <a href="{{ route('painel.relatorios.viagens.csv', request()->query()) }}" class="btn btn-outline-light btn-sm"><i class="bi bi-filetype-csv"></i> Exportar CSV</a>
                <a href="{{ $pdfUrl }}" class="btn btn-systex btn-sm"><i class="bi bi-file-earmark-pdf"></i> Gerar PDF para validação</a>
            </div>
        </div>
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-sm-6 col-lg-2"><label class="sx-label" for="data_inicio">Período inicial</label><input id="data_inicio" class="form-control" type="date" name="data_inicio" value="{{ request('data_inicio') }}"></div>
            <div class="col-sm-6 col-lg-2"><label class="sx-label" for="data_fim">Período final</label><input id="data_fim" class="form-control" type="date" name="data_fim" value="{{ request('data_fim') }}"></div>
            <div class="col-sm-6 col-lg-2"><label class="sx-label" for="cliente_id">Cliente</label><select id="cliente_id" class="form-select" name="cliente_id"><option value="">Todos</option>@foreach($clientes as $cliente)<option value="{{ $cliente->id }}" @selected((string) request('cliente_id') === (string) $cliente->id)>{{ $cliente->nome_fantasia ?: $cliente->razao_social }}</option>@endforeach</select></div>
            <div class="col-sm-6 col-lg-2"><label class="sx-label" for="motorista_id">Motorista</label><select id="motorista_id" class="form-select" name="motorista_id"><option value="">Todos</option>@foreach($motoristas as $motorista)<option value="{{ $motorista->id }}" @selected((string) request('motorista_id') === (string) $motorista->id)>{{ $motorista->name }}</option>@endforeach</select></div>
            <div class="col-sm-6 col-lg-2"><label class="sx-label" for="veiculo_id">Veículo</label><select id="veiculo_id" class="form-select" name="veiculo_id"><option value="">Todos</option>@foreach($veiculos as $veiculo)<option value="{{ $veiculo->id }}" @selected((string) request('veiculo_id') === (string) $veiculo->id)>{{ $veiculo->placa }} · {{ $veiculo->modelo }}</option>@endforeach</select></div>
            <div class="col-sm-6 col-lg-2"><label class="sx-label" for="status">Status</label><select id="status" class="form-select" name="status"><option value="">Todos</option>@foreach($statusOptions as $status => $label)<option value="{{ $status }}" @selected(request('status') === $status)>{{ $label }}</option>@endforeach</select></div>
            <div class="col-sm-6 col-lg-2"><label class="sx-label" for="natureza">Natureza</label><select id="natureza" class="form-select" name="natureza"><option value="">Todas</option><option value="programada" @selected(request('natureza') === 'programada')>Programada</option><option value="extra" @selected(request('natureza') === 'extra')>Extra</option></select></div>
            <div class="col-sm-6 col-lg-2"><label class="sx-label" for="tipo_periodo">Tipo/período</label><select id="tipo_periodo" class="form-select" name="tipo_periodo"><option value="">Todos</option><option value="diario" @selected(request('tipo_periodo') === 'diario')>Diário</option><option value="mensal" @selected(request('tipo_periodo') === 'mensal')>Mensal</option><option value="esporadico" @selected(request('tipo_periodo') === 'esporadico')>Esporádico</option></select></div>
            <div class="col-sm-6 col-lg-4 d-flex gap-2">
                <button class="btn btn-systex flex-fill"><i class="bi bi-funnel"></i> Filtrar</button>
                <a href="{{ route('painel.relatorios.index') }}" class="btn btn-outline-light flex-fill"><i class="bi bi-x-circle"></i> Limpar filtros</a>
            </div>
        </form>
    </div>

    @php
        $cards = [
            ['label' => 'Total de viagens', 'value' => $totais['total'], 'icon' => 'bi-sign-turn-right', 'tone' => 'info'],
            ['label' => 'Programadas', 'value' => $totais['programadas'], 'icon' => 'bi-calendar-check', 'tone' => 'info'],
            ['label' => 'Extras', 'value' => $totais['extras'], 'icon' => 'bi-lightning-charge', 'tone' => 'warning'],
            ['label' => 'Finalizadas', 'value' => $totais['finalizadas'], 'icon' => 'bi-check2-circle', 'tone' => 'success'],
            ['label' => 'Canceladas', 'value' => $totais['canceladas'], 'icon' => 'bi-x-circle', 'tone' => 'muted'],
            ['label' => 'Com atraso', 'value' => $totais['atrasadas'], 'icon' => 'bi-clock-history', 'tone' => $totais['atrasadas'] ? 'danger' : 'success'],
            ['label' => 'Minutos de atraso', 'value' => number_format($totais['minutos_atraso'], 0, ',', '.'), 'icon' => 'bi-hourglass-split', 'tone' => $totais['minutos_atraso'] ? 'danger' : 'success'],
            ['label' => 'Média de atraso', 'value' => number_format($totais['media_atraso'], 1, ',', '.').' min', 'icon' => 'bi-speedometer2', 'tone' => 'warning'],
            ['label' => 'Ocorrências', 'value' => $totais['ocorrencias'], 'icon' => 'bi-exclamation-diamond', 'tone' => $totais['ocorrencias'] ? 'danger' : 'success'],
            ['label' => 'Motoristas utilizados', 'value' => $totais['motoristas'], 'icon' => 'bi-person-badge', 'tone' => 'info'],
            ['label' => 'Veículos utilizados', 'value' => $totais['veiculos'], 'icon' => 'bi-truck', 'tone' => 'info'],
        ];
    @endphp
    <div class="report-kpi-grid mb-3">
        @foreach($cards as $card)
            <div class="report-kpi report-kpi-{{ $card['tone'] }}"><i class="bi {{ $card['icon'] }}"></i><div><strong>{{ $card['value'] }}</strong><span>{{ $card['label'] }}</span></div></div>
        @endforeach
    </div>

    <div class="report-insights mb-3">
        <div><span>Cliente com maior demanda</span><strong>{{ $totais['top_cliente'] ?: 'Não informado' }}</strong></div>
        <div><span>Motorista mais utilizado</span><strong>{{ $totais['top_motorista'] ?: 'Não informado' }}</strong></div>
        <div><span>Conferência para faturamento</span><strong>{{ $totais['finalizadas'] }} finalizada(s) no filtro</strong></div>
    </div>

    <div class="sx-card">
        <div class="sx-card-header"><div><h5 class="sx-card-title">Viagens encontradas</h5><div class="sx-muted small">{{ $viagens->total() }} registro(s). Use “Detalhes” para conferir atrasos e ocorrências.</div></div></div>
        @if($viagens->count())
            <div class="table-responsive sx-table-shell report-table-shell">
                <table class="table table-hover table-systex-grid report-table">
                    <thead><tr><th>ID</th><th>Prevista</th><th>Cliente / Trajeto</th><th>Motorista</th><th>Veículo</th><th>Natureza</th><th>Tipo/período</th><th>Status</th><th>Checklist</th><th>Atraso</th><th>Ocorrências</th><th>Criado em</th><th></th></tr></thead>
                    <tbody>
                    @foreach($viagens as $viagem)
                        @php
                            $atribuicao = $viagem->ultimaAtribuicao;
                            $atrasoTotal = (int) $viagem->atraso_viagem_total + (int) $viagem->atraso_passageiro_total;
                            $temDetalhes = $atrasoTotal > 0 || $viagem->ocorrencias_count > 0;
                        @endphp
                        <tr>
                            <td class="fw-bold">#{{ $viagem->id }}</td>
                            <td><strong>{{ $viagem->data_hora?->format('d/m/Y') }}</strong><div class="sx-muted small">{{ $viagem->data_hora?->format('H:i') }}</div></td>
                            <td><strong>{{ $viagem->cliente?->nome_fantasia ?: $viagem->cliente?->razao_social ?: 'Não informado' }}</strong><div class="sx-muted small report-route">{{ $viagem->origem }} → {{ $viagem->destino }}</div></td>
                            <td>{{ $atribuicao?->motorista?->name ?: 'Não informado' }}</td>
                            <td>{{ $atribuicao?->veiculo?->placa ?: 'Não informado' }}</td>
                            <td>@include('partials.panel.status-badge', ['status' => $viagem->natureza ?: 'nao_informado', 'label' => $viagem->natureza === 'extra' ? 'Extra' : ($viagem->natureza === 'programada' ? 'Programada' : 'Não informado')])</td>
                            <td>@include('partials.panel.status-badge', ['status' => $viagem->tipo_periodo ?: 'nao_informado', 'label' => ['diario'=>'Diário','mensal'=>'Mensal','esporadico'=>'Esporádico'][$viagem->tipo_periodo] ?? 'Não informado'])</td>
                            <td>@include('partials.panel.status-badge', ['status' => $viagem->status, 'label' => $viagem->statusLabel()])</td>
                            <td>@include('partials.panel.status-badge', ['status' => $viagem->ultimoChecklist?->status ?? 'pendente', 'label' => $viagem->ultimoChecklist ? ucfirst(str_replace('_', ' ', $viagem->ultimoChecklist->status)) : 'Não iniciado'])</td>
                            <td>@include('partials.panel.status-badge', ['status' => $atrasoTotal > 0 ? 'atrasada' : 'ok', 'label' => $atrasoTotal > 0 ? $atrasoTotal.' min' : 'Sem atraso'])</td>
                            <td>@include('partials.panel.status-badge', ['status' => $viagem->ocorrencias_count > 0 ? 'critica' : 'ok', 'label' => $viagem->ocorrencias_count.' ocorrência(s)'])</td>
                            <td>{{ $viagem->created_at?->format('d/m/Y H:i') }}</td>
                            <td><div class="sx-actions">
                                @if($temDetalhes)<button class="btn btn-icon btn-outline-light" type="button" data-bs-toggle="collapse" data-bs-target="#detalhes-{{ $viagem->id }}" aria-expanded="false" aria-controls="detalhes-{{ $viagem->id }}" title="Atrasos e ocorrências"><i class="bi bi-list-ul"></i></button>@endif
                                <a class="btn btn-icon btn-outline-light" href="{{ route('painel.operador.solicitacoes.show', $viagem->id) }}" title="Abrir viagem"><i class="bi bi-arrow-up-right"></i></a>
                            </div></td>
                        </tr>
                        @if($temDetalhes)
                            <tr class="collapse report-detail-row" id="detalhes-{{ $viagem->id }}">
                                <td colspan="13">
                                    <div class="report-detail-grid">
                                        <section><h6><i class="bi bi-clock-history"></i> Atrasos</h6>
                                            @forelse($viagem->atrasosViagem as $atraso)<div class="report-event"><strong>Viagem · {{ $atraso->minutos_atraso }} min</strong><span>{{ $atraso->motivo ?: 'Sem motivo informado' }}</span><small>Ocorrido em {{ ($atraso->ocorrido_em ?? $atraso->created_at)?->format('d/m/Y H:i') }} · registrado em {{ $atraso->created_at?->format('d/m/Y H:i') }}</small></div>@empty @endforelse
                                            @forelse($viagem->atrasosPassageiro as $atraso)<div class="report-event"><strong>Passageiro {{ $atraso->passageiro?->nome ?: 'não informado' }} · {{ $atraso->minutos_atraso }} min</strong><span>{{ $atraso->motivo ?: 'Sem motivo informado' }}</span><small>Ocorrido em {{ ($atraso->ocorrido_em ?? $atraso->created_at)?->format('d/m/Y H:i') }} · registrado em {{ $atraso->created_at?->format('d/m/Y H:i') }}</small></div>@empty @endforelse
                                            @if($atrasoTotal === 0)<div class="sx-muted small">Nenhum atraso registrado.</div>@endif
                                        </section>
                                        <section><h6><i class="bi bi-exclamation-diamond"></i> Ocorrências</h6>
                                            @forelse($viagem->ocorrencias as $ocorrencia)<div class="report-event"><strong>{{ $ocorrencia->tipo }}</strong><span>{{ $ocorrencia->descricao }}</span><small>Ocorrido em {{ ($ocorrencia->ocorrido_em ?? $ocorrencia->registrado_em ?? $ocorrencia->created_at)?->format('d/m/Y H:i') }} · registrado em {{ $ocorrencia->created_at?->format('d/m/Y H:i') }} · {{ $ocorrencia->responsavel?->name ?: 'Responsável não informado' }}</small></div>@empty<div class="sx-muted small">Nenhuma ocorrência registrada.</div>@endforelse
                                        </section>
                                    </div>
                                </td>
                            </tr>
                        @endif
                    @endforeach
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mt-3"><div class="sx-muted small">Exibindo {{ $viagens->firstItem() }}–{{ $viagens->lastItem() }} de {{ $viagens->total() }}</div>{{ $viagens->links() }}</div>
        @else
            @include('partials.panel.empty-state', ['title' => 'Nenhuma viagem encontrada', 'message' => 'Ajuste os filtros ou limpe a busca para visualizar outras viagens.', 'icon' => 'bi bi-file-earmark-bar-graph'])
        @endif
    </div>
</div>
@endsection
