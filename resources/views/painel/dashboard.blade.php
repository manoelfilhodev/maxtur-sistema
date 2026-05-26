@extends('layouts.app')

@section('page-heading')
    <div class="sx-page-header">
        <div>
            <h3 class="sx-page-title">Dashboard</h3>
            <div class="sx-page-subtitle">Visão geral de viagens, programação, atrasos e bloqueios operacionais</div>
        </div>
        <div class="sx-page-actions">
            <a href="{{ route('painel.operador.solicitacoes.index') }}" class="btn btn-systex btn-sm">
                <i class="bi bi-sign-turn-right"></i> Ver viagens
            </a>
        </div>
    </div>
@endsection

@section('content')
<div class="sx-container">
    @php
        $cards = [
            ['label' => 'Total de viagens', 'value' => $metricas['total'], 'icon' => 'bi-sign-turn-right'],
            ['label' => 'Solicitadas', 'value' => $metricas['solicitadas'], 'icon' => 'bi-inbox'],
            ['label' => 'Programadas', 'value' => $metricas['programadas'], 'icon' => 'bi-calendar-check'],
            ['label' => 'Em andamento', 'value' => $metricas['em_andamento'], 'icon' => 'bi-play-circle'],
            ['label' => 'Atrasadas', 'value' => $metricas['atrasadas'], 'icon' => 'bi-clock-history'],
            ['label' => 'Finalizadas', 'value' => $metricas['finalizadas'], 'icon' => 'bi-check2-circle'],
            ['label' => 'Bloqueadas', 'value' => $metricas['bloqueadas'], 'icon' => 'bi-shield-exclamation'],
        ];
    @endphp

    <div class="row g-3 mb-3">
        @foreach($cards as $card)
            <div class="col-xl-3 col-md-4 col-sm-6">
                <div class="sx-card h-100">
                    <div class="d-flex align-items-start justify-content-between">
                        <div>
                            <div class="sx-muted small">{{ $card['label'] }}</div>
                            <div class="fs-2 fw-bold text-white mt-2">{{ $card['value'] }}</div>
                        </div>
                        <div class="sx-card-icon"><i class="bi {{ $card['icon'] }}"></i></div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="sx-card">
        <div class="sx-card-header">
            <div>
                <h5 class="sx-card-title">Últimas viagens</h5>
                <div class="sx-muted small">Solicitações recentes e situação operacional</div>
            </div>
            <a href="{{ route('painel.operador.solicitacoes.index') }}" class="btn btn-outline-light btn-sm">Ver tudo</a>
        </div>

        @if($ultimasSolicitacoes->count())
            <div class="table-responsive sx-table-shell">
                <table class="table table-hover table-systex-grid">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Cliente</th>
                            <th>Trajeto</th>
                            <th>Data/Hora</th>
                            <th>Status</th>
                            <th>Veículo</th>
                            <th>Motorista</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($ultimasSolicitacoes as $solicitacao)
                        @php($atribuicao = $solicitacao->atribuicoes->last())
                        <tr>
                            <td class="fw-semibold">#{{ $solicitacao->id }}</td>
                            <td>{{ $solicitacao->cliente->nome_fantasia ?? $solicitacao->cliente->razao_social ?? '-' }}</td>
                            <td>{{ $solicitacao->origem }} → {{ $solicitacao->destino }}</td>
                            <td>{{ optional($solicitacao->data_hora)->format('d/m/Y H:i') }}</td>
                            <td>@include('partials.panel.status-badge', ['status' => $solicitacao->status, 'label' => $solicitacao->statusLabel()])</td>
                            <td>{{ $atribuicao?->veiculo?->placa ?? '-' }}</td>
                            <td>{{ $atribuicao?->motorista?->name ?? '-' }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        @else
            @include('partials.panel.empty-state', [
                'title' => 'Nenhuma viagem cadastrada',
                'message' => 'Crie a primeira solicitação de viagem para iniciar o acompanhamento operacional.',
                'actionRoute' => route('painel.operador.solicitacoes.create'),
                'actionLabel' => 'Nova viagem',
                'icon' => 'bi bi-sign-turn-right',
            ])
        @endif
    </div>
</div>
@endsection
