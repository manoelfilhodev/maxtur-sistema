@extends('layouts.app')

@section('page-heading')
    @include('partials.panel.page-header', [
        'title' => 'Relatórios',
        'subtitle' => 'Indicadores operacionais do MaxTur',
    ])
@endsection

@section('content')
<div class="sx-container">
    <div class="sx-card">
        @include('partials.panel.empty-state', [
            'title' => 'Relatórios avançados em preparação',
            'message' => 'Para a apresentação do MVP, o histórico operacional estará no detalhe das viagens. Exportações em Excel e PDF ficam para o pós-MVP.',
            'icon' => 'bi bi-file-earmark-bar-graph',
        ])
    </div>
</div>
@endsection
