@extends('layouts.app')

@section('page-heading')
    <div class="sx-page-header">
        <div>
            <h3 class="sx-page-title">{{ $motorista->name }}</h3>
            <div class="sx-page-subtitle">Perfil operacional do motorista</div>
        </div>
        <div class="sx-page-actions">
            <a class="btn btn-outline-light btn-sm" href="{{ route('master.motoristas.index') }}">
                <i class="bi bi-arrow-left"></i> Voltar
            </a>
            <a class="btn btn-systex btn-sm" href="{{ route('master.motoristas.edit', $motorista->id) }}">
                <i class="bi bi-pencil-square"></i> Editar
            </a>
        </div>
    </div>
@endsection

@section('content')
<div class="sx-container">
    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="sx-card">
        <div class="row g-3">
            <div class="col-md-6">
                <div class="sx-kicker">E-mail</div>
                <div class="text-white">{{ $motorista->email }}</div>
            </div>
            <div class="col-md-3">
                <div class="sx-kicker">Telefone</div>
                <div class="text-white">{{ $motorista->telefone ?: '-' }}</div>
            </div>
            <div class="col-md-3">
                <div class="sx-kicker">Documento/CNH</div>
                <div class="text-white">{{ $motorista->cpf ?: '-' }}</div>
            </div>
            <div class="col-md-3">
                <div class="sx-kicker">Status</div>
                @include('partials.panel.status-badge', ['status' => $motorista->ativo ? 'ativo' : 'inativo'])
            </div>
            <div class="col-md-3">
                <div class="sx-kicker">Perfil</div>
                <span class="sx-badge sx-badge-info">{{ strtoupper($motorista->role ?? 'MOTORISTA') }}</span>
            </div>
        </div>
    </div>
</div>
@endsection
