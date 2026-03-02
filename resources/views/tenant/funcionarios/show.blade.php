@extends('layouts.app')

@section('page-heading')
<div class="d-flex align-items-start justify-content-between flex-wrap gap-2">
    <div>
        <h3 class="fw-bold mb-1 text-white">Detalhes do funcionário</h3>
        <div class="text-muted" style="color: rgba(255,255,255,.65) !important;">
            {{ $funcionario->name }}
        </div>
    </div>
    <a href="{{ route('tenant.funcionarios.index') }}" class="btn btn-outline-light btn-sm">Voltar</a>
</div>
@endsection

@section('content')
<div class="dash-card p-3">
    <div class="row g-3">
        <div class="col-md-6">
            <div class="text-muted small">Nome</div>
            <div class="fw-semibold text-white">{{ $funcionario->name }}</div>
        </div>
        <div class="col-md-6">
            <div class="text-muted small">Email</div>
            <div class="fw-semibold text-white">{{ $funcionario->email }}</div>
        </div>
        <div class="col-md-6">
            <div class="text-muted small">Perfil</div>
            <div><span class="badge bg-secondary">{{ $funcionario->role }}</span></div>
        </div>
        <div class="col-md-6">
            <div class="text-muted small">Cargo</div>
            <div class="fw-semibold text-white">{{ $funcionario->cargo ?: '-' }}</div>
        </div>
        <div class="col-md-6">
            <div class="text-muted small">Telefone</div>
            <div class="fw-semibold text-white">{{ $funcionario->telefone ?: '-' }}</div>
        </div>
        <div class="col-md-6">
            <div class="text-muted small">Endereço</div>
            <div class="fw-semibold text-white">{{ $funcionario->endereco ?: '-' }}</div>
        </div>
        <div class="col-md-6">
            <div class="text-muted small">Status da conta</div>
            <div>
                @if($funcionario->activated_at)
                    <span class="badge bg-success">Ativado</span>
                @else
                    <span class="badge bg-warning text-dark">Pendente</span>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

