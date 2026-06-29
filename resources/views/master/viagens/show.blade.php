@extends('layouts.app')

@section('page-heading')
@include('partials.panel.page-header', [
    'title' => 'Viagem #'.$viagem->id,
    'subtitle' => 'Detalhes da programação e execução da viagem',
    'backRoute' => route('master.viagens.index'),
])
@endsection

@section('content')
<div class="sx-container">
<div class="sx-card">
    <div class="row g-4">
        <div class="col-md-4"><div class="sx-kicker">Cliente</div><div class="fw-semibold">{{ $viagem->cliente->nome_fantasia ?: $viagem->cliente->razao_social }}</div></div>
        <div class="col-md-4"><div class="sx-kicker">Veículo</div><div>{{ $viagem->veiculo->placa }} · {{ $viagem->veiculo->modelo }}</div></div>
        <div class="col-md-4"><div class="sx-kicker">Motorista</div><div>{{ $viagem->motorista->nome }}</div></div>
        <div class="col-md-6"><div class="sx-kicker">Origem</div><div>{{ $viagem->origem }}</div></div>
        <div class="col-md-6"><div class="sx-kicker">Destino</div><div>{{ $viagem->destino }}</div></div>
        <div class="col-md-3"><div class="sx-kicker">Prevista</div><div>{{ optional($viagem->data_prevista)->format('d/m/Y H:i') }}</div></div>
        <div class="col-md-3"><div class="sx-kicker">Realizada</div><div>{{ optional($viagem->data_real)->format('d/m/Y H:i') ?: '-' }}</div></div>
        <div class="col-md-2"><div class="sx-kicker">Status</div>@include('partials.panel.status-badge', ['status' => $viagem->status])</div>
        <div class="col-md-2"><div class="sx-kicker">Período</div><div>{{ ['diario'=>'Diário','mensal'=>'Mensal','esporadico'=>'Esporádico'][$viagem->tipo_periodo] ?? $viagem->tipo_periodo }}</div></div>
        <div class="col-md-2"><div class="sx-kicker">Natureza</div>@include('partials.panel.status-badge', ['status' => $viagem->natureza])</div>
        <div class="col-12"><div class="sx-kicker">Observações</div><div>{{ $viagem->observacoes ?: '-' }}</div></div>
    </div>
</div>
</div>
@endsection
