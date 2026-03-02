@extends('layouts.app')

@section('page-heading')
<h3 class="text-white mb-0">Master - Cliente #{{ $cliente->id }}</h3>
@endsection

@section('content')
@if(session('ativacao_link'))
    <div class="alert alert-warning">
        <strong>Conta do cliente criada. Envie este link para ativacao:</strong><br>
        Email: {{ session('ativacao_link.email') }}<br>
        <a href="{{ session('ativacao_link.link') }}" target="_blank">{{ session('ativacao_link.link') }}</a>
    </div>
@endif

<div class="dash-card p-3">
    <p><strong>Razao Social:</strong> {{ $cliente->razao_social }}</p>
    <p><strong>Fantasia:</strong> {{ $cliente->nome_fantasia ?: '-' }}</p>
    <p><strong>CNPJ:</strong> {{ $cliente->cnpj ?: '-' }}</p>
    <p><strong>Usuarios Cliente:</strong> {{ $cliente->client_users_count }}</p>
    <p><strong>Viagens atribuidas:</strong> {{ $cliente->viagens_count }}</p>
    @php
        $clientAdmin = $cliente->clientUsers->firstWhere('role', 'CLIENT_ADMIN');
    @endphp
    <p><strong>CLIENT_ADMIN:</strong> {{ $clientAdmin?->email ?? 'Nao encontrado' }}</p>
    <p><strong>Ativada em:</strong> {{ $clientAdmin?->activated_at?->format('d/m/Y H:i') ?? 'Pendente' }}</p>

    @if($clientAdmin)
        <form method="POST" action="{{ route('master.clientes.reenviar-ativacao', $cliente->id) }}" class="mb-3">
            @csrf
            <button class="btn btn-systex btn-sm">Gerar novo link de ativacao</button>
        </form>
    @endif

    <a class="btn btn-outline-light btn-sm" href="{{ route('master.clientes.index') }}">Voltar</a>
</div>
@endsection
