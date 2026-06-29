@extends('layouts.app')

@section('page-heading')
    @include('partials.panel.page-header', [
        'title' => 'Validação do motorista',
        'subtitle' => 'Conferência das viagens lançadas e da base de remuneração do período',
    ])
@endsection

@section('content')
<div class="sx-container travel-report">
    @include('partials.panel.flash-messages')
    @include('partials.panel.form-errors')

    <div class="sx-card mb-3">
        <div class="sx-card-header">
            <div>
                <h5 class="sx-card-title">Período e motorista</h5>
                <div class="sx-muted small">Por segurança, somente viagens finalizadas entram automaticamente no cálculo por viagem.</div>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <a href="{{ route('painel.relatorios.index') }}" class="btn btn-outline-light btn-sm"><i class="bi bi-bar-chart"></i> Relatório gerencial</a>
                <a href="{{ route('painel.relatorios.motoristas.csv', request()->query()) }}" class="btn btn-outline-light btn-sm"><i class="bi bi-filetype-csv"></i> Exportar CSV</a>
                <a href="{{ $pdfUrl }}" class="btn btn-systex btn-sm"><i class="bi bi-file-earmark-pdf"></i> Exportar PDF</a>
            </div>
        </div>

        <form method="GET" class="row g-3 align-items-end">
            <div class="col-sm-6 col-lg-2"><label class="sx-label" for="data_inicio">Período inicial</label><input id="data_inicio" class="form-control" type="date" name="data_inicio" value="{{ request('data_inicio') }}" required></div>
            <div class="col-sm-6 col-lg-2"><label class="sx-label" for="data_fim">Período final</label><input id="data_fim" class="form-control" type="date" name="data_fim" value="{{ request('data_fim') }}" required></div>
            <div class="col-sm-6 col-lg-3"><label class="sx-label" for="motorista_id">Motorista</label><select id="motorista_id" class="form-select" name="motorista_id"><option value="">Todos os motoristas</option>@foreach($motoristas as $item)<option value="{{ $item->id }}" @selected((string) request('motorista_id') === (string) $item->id)>{{ $item->name }} · {{ $item->tipo_recebimento === 'por_viagem' ? 'por viagem' : 'salário' }}</option>@endforeach</select></div>
            <div class="col-sm-6 col-lg-2"><label class="sx-label" for="cliente_id">Cliente</label><select id="cliente_id" class="form-select" name="cliente_id"><option value="">Todos</option>@foreach($clientes as $cliente)<option value="{{ $cliente->id }}" @selected((string) request('cliente_id') === (string) $cliente->id)>{{ $cliente->nome_fantasia ?: $cliente->razao_social }}</option>@endforeach</select></div>
            <div class="col-sm-6 col-lg-2"><label class="sx-label" for="status">Status</label><select id="status" class="form-select" name="status"><option value="">Todos</option>@foreach($statusOptions as $status => $label)<option value="{{ $status }}" @selected(request('status') === $status)>{{ $label }}</option>@endforeach</select></div>
            <div class="col-sm-6 col-lg-2"><label class="sx-label" for="natureza">Natureza</label><select id="natureza" class="form-select" name="natureza"><option value="">Todas</option><option value="programada" @selected(request('natureza') === 'programada')>Programada</option><option value="extra" @selected(request('natureza') === 'extra')>Extra</option></select></div>
            <div class="col-sm-6 col-lg-2"><label class="sx-label" for="tipo_periodo">Tipo/período</label><select id="tipo_periodo" class="form-select" name="tipo_periodo"><option value="">Todos</option><option value="diario" @selected(request('tipo_periodo') === 'diario')>Diário</option><option value="mensal" @selected(request('tipo_periodo') === 'mensal')>Mensal</option><option value="esporadico" @selected(request('tipo_periodo') === 'esporadico')>Esporádico</option></select></div>
            <div class="col-sm-6 col-lg-4 d-flex gap-2"><button class="btn btn-systex flex-fill"><i class="bi bi-funnel"></i> Conferir</button><a href="{{ route('painel.relatorios.motoristas.index') }}" class="btn btn-outline-light flex-fill"><i class="bi bi-arrow-counterclockwise"></i> Mês atual</a></div>
        </form>
    </div>

    @if($motorista)
        <div class="report-insights mb-3">
            <div><span>Motorista selecionado</span><strong>{{ $motorista->name }}</strong></div>
            <div><span>Regra cadastrada</span><strong>{{ $motorista->tipo_recebimento === 'por_viagem' ? 'R$ '.number_format((float) $motorista->valor_por_viagem, 2, ',', '.').' por viagem' : 'Salário de R$ '.number_format((float) $motorista->valor_salario, 2, ',', '.') }}</strong></div>
            <div><span>CPF</span><strong>{{ $motorista->cpf ?: 'Não informado' }}</strong></div>
        </div>
    @endif

    @php
        $cards = [
            ['label' => 'Viagens no filtro', 'value' => $totais['viagens'], 'icon' => 'bi-sign-turn-right', 'tone' => 'info'],
            ['label' => 'Finalizadas', 'value' => $totais['finalizadas'], 'icon' => 'bi-check2-circle', 'tone' => 'success'],
            ['label' => 'Extras', 'value' => $totais['extras'], 'icon' => 'bi-lightning-charge', 'tone' => 'warning'],
            ['label' => 'Canceladas', 'value' => $totais['canceladas'], 'icon' => 'bi-x-circle', 'tone' => 'muted'],
            ['label' => 'Minutos de atraso', 'value' => number_format($totais['minutos_atraso'], 0, ',', '.'), 'icon' => 'bi-clock-history', 'tone' => $totais['minutos_atraso'] ? 'danger' : 'success'],
            ['label' => 'Ocorrências', 'value' => $totais['ocorrencias'], 'icon' => 'bi-exclamation-diamond', 'tone' => $totais['ocorrencias'] ? 'danger' : 'success'],
            ['label' => 'Calculado por viagem', 'value' => 'R$ '.number_format($totais['valor_calculado'], 2, ',', '.'), 'icon' => 'bi-cash-stack', 'tone' => 'success'],
        ];
    @endphp
    <div class="report-kpi-grid mb-3">@foreach($cards as $card)<div class="report-kpi report-kpi-{{ $card['tone'] }}"><i class="bi {{ $card['icon'] }}"></i><div><strong>{{ $card['value'] }}</strong><span>{{ $card['label'] }}</span></div></div>@endforeach</div>

    @if($motorista?->tipo_recebimento === 'salario')
        <div class="alert alert-info"><i class="bi bi-info-circle me-2"></i>O salário contratual é exibido para conferência, mas não é somado automaticamente: proporcionalidade, adicionais, descontos e benefícios dependem das regras da folha.</div>
    @endif

    <div class="sx-card">
        <div class="sx-card-header"><div><h5 class="sx-card-title">Memória de cálculo</h5><div class="sx-muted small">Cada linha mostra o lançamento que sustenta a conferência com o motorista.</div></div></div>
        @if($viagens->count())
            <div class="table-responsive sx-table-shell report-table-shell">
                <table class="table table-hover table-systex-grid report-table">
                    <thead><tr><th>Viagem</th><th>Data/hora</th><th>Motorista</th><th>Cliente / Trajeto</th><th>Veículo</th><th>Natureza</th><th>Status</th><th>Atrasos</th><th>Ocorrências</th><th>Valor</th><th></th></tr></thead>
                    <tbody>@foreach($viagens as $viagem)
                        @php
                            $condutor = $viagem->ultimaAtribuicao?->motorista;
                            $atraso = (int) $viagem->atraso_viagem_total + (int) $viagem->atraso_passageiro_total;
                            $elegivel = $viagem->status === \App\Support\ViagemStatus::FINALIZADA && $condutor?->tipo_recebimento === 'por_viagem';
                        @endphp
                        <tr>
                            <td class="fw-bold">#{{ $viagem->id }}</td><td><strong>{{ $viagem->data_hora?->format('d/m/Y') }}</strong><div class="sx-muted small">{{ $viagem->data_hora?->format('H:i') }}</div></td>
                            <td>{{ $condutor?->name ?: 'Não informado' }}<div class="sx-muted small">{{ $condutor?->tipo_recebimento === 'por_viagem' ? 'Por viagem' : 'Salário' }}</div></td>
                            <td><strong>{{ $viagem->cliente?->nome_fantasia ?: $viagem->cliente?->razao_social ?: 'Não informado' }}</strong><div class="sx-muted small report-route">{{ $viagem->origem }} → {{ $viagem->destino }}</div></td>
                            <td>{{ $viagem->ultimaAtribuicao?->veiculo?->placa ?: 'Não informado' }}</td>
                            <td>@include('partials.panel.status-badge', ['status' => $viagem->natureza, 'label' => $viagem->natureza === 'extra' ? 'Extra' : 'Programada'])</td>
                            <td>@include('partials.panel.status-badge', ['status' => $viagem->status, 'label' => $viagem->statusLabel()])</td>
                            <td>@include('partials.panel.status-badge', ['status' => $atraso ? 'atrasada' : 'ok', 'label' => $atraso ? $atraso.' min' : 'Sem atraso'])</td>
                            <td>{{ $viagem->ocorrencias_count }}</td>
                            <td><strong>{{ $elegivel ? 'R$ '.number_format((float) $condutor->valor_por_viagem, 2, ',', '.') : '—' }}</strong><div class="sx-muted small">{{ $elegivel ? 'Elegível' : ($condutor?->tipo_recebimento === 'salario' ? 'Regime salarial' : 'Não finalizada') }}</div></td>
                            <td><a class="btn btn-icon btn-outline-light" href="{{ route('painel.operador.solicitacoes.show', $viagem->id) }}" title="Abrir viagem"><i class="bi bi-arrow-up-right"></i></a></td>
                        </tr>
                    @endforeach</tbody>
                </table>
            </div>
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mt-3"><div class="sx-muted small">Exibindo {{ $viagens->firstItem() }}–{{ $viagens->lastItem() }} de {{ $viagens->total() }}</div>{{ $viagens->links() }}</div>
        @else
            @include('partials.panel.empty-state', ['title' => 'Nenhum lançamento encontrado', 'message' => 'Ajuste o motorista, o período ou o status para localizar viagens.', 'icon' => 'bi bi-person-check'])
        @endif
    </div>
</div>
@endsection
