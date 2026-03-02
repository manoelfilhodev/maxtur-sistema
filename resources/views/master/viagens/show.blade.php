@extends('layouts.app')

@section('page-heading')
<h3 class="text-white mb-0">Master - Viagem #{{ $viagem->id }}</h3>
@endsection

@section('content')
<div class="dash-card p-3">
    <p><strong>Cliente:</strong> {{ $viagem->cliente->nome_fantasia ?: $viagem->cliente->razao_social }}</p>
    <p><strong>Veiculo:</strong> {{ $viagem->veiculo->placa }} - {{ $viagem->veiculo->modelo }}</p>
    <p><strong>Motorista:</strong> {{ $viagem->motorista->nome }}</p>
    <p><strong>Origem:</strong> {{ $viagem->origem }}</p>
    <p><strong>Destino:</strong> {{ $viagem->destino }}</p>
    <p><strong>Data Prevista:</strong> {{ optional($viagem->data_prevista)->format('d/m/Y H:i') }}</p>
    <p><strong>Data Real:</strong> {{ optional($viagem->data_real)->format('d/m/Y H:i') ?: '-' }}</p>
    <p><strong>Status:</strong> {{ $viagem->status }}</p>
    <p><strong>Observacoes:</strong> {{ $viagem->observacoes ?: '-' }}</p>
    <a class="btn btn-outline-light btn-sm" href="{{ route('master.viagens.index') }}">Voltar</a>
</div>
@endsection

