@extends('layouts.app')

@section('page-heading')
<div class="d-flex align-items-start justify-content-between flex-wrap gap-2">
    <div>
        <h3 class="fw-bold mb-1 text-white">Relatórios do cliente</h3>
        <div class="text-muted" style="color: rgba(255,255,255,.65) !important;">
            Indicadores de viagens, pontualidade, veículos e motoristas
        </div>
    </div>
</div>
@endsection

@section('content')
<div class="dash-card p-3 mb-3">
    <form method="GET" class="row g-2">
        <div class="col-md-3">
            <select class="form-select" name="status">
                <option value="">Todos status</option>
                @foreach(['programada','em_andamento','realizada','cancelada','atrasada'] as $status)
                    <option value="{{ $status }}" @selected(request('status') === $status)>{{ $status }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <input class="form-control" type="date" name="data_inicio" value="{{ request('data_inicio') }}">
        </div>
        <div class="col-md-3">
            <input class="form-control" type="date" name="data_fim" value="{{ request('data_fim') }}">
        </div>
        <div class="col-md-2">
            <button class="btn btn-outline-light w-100">Aplicar</button>
        </div>
    </form>
</div>

<div class="row g-3 mb-3">
    <div class="col-md-4"><div class="dash-card p-3"><div class="text-muted small">Pontuais</div><div class="fs-4 fw-bold text-white">{{ $pontuais }}</div></div></div>
    <div class="col-md-4"><div class="dash-card p-3"><div class="text-muted small">Atrasadas</div><div class="fs-4 fw-bold text-white">{{ $atrasadas }}</div></div></div>
    <div class="col-md-4"><div class="dash-card p-3"><div class="text-muted small">Total no filtro</div><div class="fs-4 fw-bold text-white">{{ $viagens->total() }}</div></div></div>
</div>

<div class="row g-3 mb-3">
    <div class="col-md-4">
        <div class="dash-card p-3 h-100">
            <h5 class="text-white">Status</h5>
            <ul class="mb-0">
                @forelse($statusResumo as $status => $total)
                    <li>{{ $status }}: {{ $total }}</li>
                @empty
                    <li>Sem dados.</li>
                @endforelse
            </ul>
        </div>
    </div>
    <div class="col-md-4">
        <div class="dash-card p-3 h-100">
            <h5 class="text-white">Veículos usados</h5>
            <ul class="mb-0">
                @forelse($veiculosUsados as $item)
                    <li>{{ $item->veiculo->placa ?? 'N/A' }} - {{ $item->total }} viagem(ns)</li>
                @empty
                    <li>Sem dados.</li>
                @endforelse
            </ul>
        </div>
    </div>
    <div class="col-md-4">
        <div class="dash-card p-3 h-100">
            <h5 class="text-white">Motoristas usados</h5>
            <ul class="mb-0">
                @forelse($motoristasUsados as $item)
                    <li>{{ $item->motorista->nome ?? 'N/A' }} - {{ $item->total }} viagem(ns)</li>
                @empty
                    <li>Sem dados.</li>
                @endforelse
            </ul>
        </div>
    </div>
</div>

<div class="dash-card p-3">
    <h5 class="text-white">Viagens no período</h5>
    <div class="table-responsive">
        <table class="table table-dark table-hover">
            <thead><tr><th>ID</th><th>Origem</th><th>Destino</th><th>Prevista</th><th>Real</th><th>Status</th></tr></thead>
            <tbody>
                @forelse($viagens as $viagem)
                    <tr>
                        <td>{{ $viagem->id }}</td>
                        <td>{{ $viagem->origem }}</td>
                        <td>{{ $viagem->destino }}</td>
                        <td>{{ optional($viagem->data_prevista)->format('d/m/Y H:i') }}</td>
                        <td>{{ optional($viagem->data_real)->format('d/m/Y H:i') ?: '-' }}</td>
                        <td><span class="badge bg-secondary">{{ $viagem->status }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">Sem viagens.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-3 d-flex justify-content-end">{{ $viagens->links() }}</div>
</div>
@endsection

