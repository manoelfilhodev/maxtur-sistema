@extends('layouts.app')

@section('page-heading')
<h3 class="text-white mb-0">Master - Veiculo {{ $veiculo->placa }}</h3>
@endsection

@section('content')
<div class="dash-card p-3">
    <p><strong>Placa:</strong> {{ $veiculo->placa }}</p>
    <p><strong>Modelo:</strong> {{ $veiculo->modelo }}</p>
    <p><strong>Capacidade:</strong> {{ $veiculo->capacidade_passageiros }}</p>
    <p><strong>Status:</strong> {{ $veiculo->status_operacional }}</p>
    <a class="btn btn-outline-light btn-sm" href="{{ route('master.veiculos.index') }}">Voltar</a>
</div>
@endsection

