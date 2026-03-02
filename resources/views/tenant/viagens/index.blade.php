@extends('layouts.app')

@section('page-heading')
<div class="d-flex align-items-start justify-content-between flex-wrap gap-2">
    <div>
        <h3 class="fw-bold mb-1 text-white">Minhas viagens</h3>
        <div class="text-muted" style="color: rgba(255,255,255,.65) !important;">
            Consulte todas as viagens vinculadas ao seu cliente
        </div>
    </div>
</div>
@endsection

@section('content')
<div class="dash-card p-3">
    <form method="GET" class="row g-2 mb-3">
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
            <button class="btn btn-outline-light w-100">Filtrar</button>
        </div>
    </form>

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
            @forelse($viagens as $viagem)
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
                <tr><td colspan="7" class="text-center text-muted py-4">Sem viagens no filtro informado.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-3 d-flex justify-content-end">{{ $viagens->links() }}</div>
</div>
@endsection

