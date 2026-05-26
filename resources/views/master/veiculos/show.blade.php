@extends('layouts.app')

@section('page-heading')
    <div class="sx-page-header">
        <div>
            <h3 class="sx-page-title">Veículo {{ $veiculo->placa }}</h3>
            <div class="sx-page-subtitle">Dados operacionais do veículo</div>
        </div>
        <div class="sx-page-actions">
            <a class="btn btn-outline-light btn-sm" href="{{ route('master.veiculos.index') }}">
                <i class="bi bi-arrow-left"></i> Voltar
            </a>
        </div>
    </div>
@endsection

@section('content')
<div class="sx-container">
    <div class="sx-card">
        <div class="row g-3">
            <div class="col-md-3">
                <div class="sx-kicker">Placa</div>
                <div class="text-white fw-semibold">{{ $veiculo->placa }}</div>
            </div>
            <div class="col-md-4">
                <div class="sx-kicker">Modelo</div>
                <div class="text-white">{{ $veiculo->modelo }}</div>
            </div>
            <div class="col-md-3">
                <div class="sx-kicker">Capacidade</div>
                <div class="text-white">{{ $veiculo->capacidade_passageiros }} passageiros</div>
            </div>
            <div class="col-md-2">
                <div class="sx-kicker">Status</div>
                @include('partials.panel.status-badge', ['status' => $veiculo->status_operacional ?: 'liberado'])
            </div>
        </div>
    </div>
</div>
@endsection
