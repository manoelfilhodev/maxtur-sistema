@extends('layouts.app')

@section('page-heading')
<div class="d-flex align-items-start justify-content-between flex-wrap gap-2">
    <div>
        <h3 class="fw-bold mb-1 text-white">Visão geral do cliente</h3>
        <div class="text-muted" style="color: rgba(255,255,255,.65) !important;">
            Resumo operacional das viagens e dos colaboradores
        </div>
    </div>
    <div class="d-flex gap-2">
        <a class="btn btn-outline-light btn-sm" href="{{ route('tenant.viagens.index') }}">Ver viagens</a>
        <a class="btn btn-systex btn-sm" href="{{ route('tenant.funcionarios.index') }}">Gerir funcionários</a>
    </div>
</div>
@endsection

@section('content')
<div class="row g-3 mb-3">
    <div class="col-md-4 col-lg-2">
        <div class="dash-card p-3 h-100">
            <div class="text-muted small">Viagens hoje</div>
            <div class="fs-4 fw-bold text-white">{{ $viagensHoje }}</div>
        </div>
    </div>
    <div class="col-md-4 col-lg-2">
        <div class="dash-card p-3 h-100">
            <div class="text-muted small">Em andamento</div>
            <div class="fs-4 fw-bold text-white">{{ $viagensEmAndamento }}</div>
        </div>
    </div>
    <div class="col-md-4 col-lg-2">
        <div class="dash-card p-3 h-100">
            <div class="text-muted small">Programadas</div>
            <div class="fs-4 fw-bold text-white">{{ $viagensProgramadas }}</div>
        </div>
    </div>
    <div class="col-md-6 col-lg-3">
        <div class="dash-card p-3 h-100">
            <div class="text-muted small">Funcionários ativos</div>
            <div class="fs-4 fw-bold text-white">{{ $funcionariosAtivos }}</div>
        </div>
    </div>
    <div class="col-md-6 col-lg-3">
        <div class="dash-card p-3 h-100">
            <div class="text-muted small">Convites pendentes</div>
            <div class="fs-4 fw-bold text-white">{{ $funcionariosPendentes }}</div>
        </div>
    </div>
</div>

<div class="dash-card p-3">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <h5 class="mb-0 text-white">Próximas viagens</h5>
        <a class="btn btn-outline-light btn-sm" href="{{ route('tenant.viagens.index') }}">Abrir lista completa</a>
    </div>
    <div class="table-responsive">
        <table class="table table-dark table-hover">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Origem</th>
                    <th>Destino</th>
                    <th>Prevista</th>
                    <th>Status</th>
                    <th>Veículo</th>
                    <th>Motorista</th>
                </tr>
            </thead>
            <tbody>
            @forelse($proximasViagens as $viagem)
                <tr>
                    <td>{{ $viagem->id }}</td>
                    <td>{{ $viagem->origem }}</td>
                    <td>{{ $viagem->destino }}</td>
                    <td>{{ optional($viagem->data_prevista)->format('d/m/Y H:i') }}</td>
                    <td><span class="badge bg-secondary">{{ $viagem->status }}</span></td>
                    <td>{{ $viagem->veiculo->placa ?? '-' }}</td>
                    <td>{{ $viagem->motorista->nome ?? '-' }}</td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center text-muted py-4">Sem viagens futuras cadastradas.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

