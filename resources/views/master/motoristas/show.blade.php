@extends('layouts.app')

@section('page-heading')
<h3 class="text-white mb-0">Master - Motorista {{ $motorista->nome }}</h3>
@endsection

@section('content')
<div class="dash-card p-3">
    <p><strong>Nome:</strong> {{ $motorista->nome }}</p>
    <p><strong>CNH:</strong> {{ $motorista->cnh ?: '-' }}</p>
    <p><strong>Telefone:</strong> {{ $motorista->telefone ?: '-' }}</p>
    <p><strong>Status:</strong> {{ $motorista->ativo ? 'Ativo' : 'Inativo' }}</p>
    <a class="btn btn-outline-light btn-sm" href="{{ route('master.motoristas.index') }}">Voltar</a>
</div>
@endsection

