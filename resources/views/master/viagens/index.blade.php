@extends('layouts.app')

@section('page-heading')
<h3 class="text-white mb-0">Master - Viagens</h3>
@endsection

@section('content')
<div class="dash-card p-3">
    <form method="GET" class="row g-2 mb-3">
        <div class="col-md-3">
            <select name="status" class="form-select">
                <option value="">Todos status</option>
                @foreach(['programada','em_andamento','realizada','cancelada','atrasada'] as $status)
                    <option value="{{ $status }}" @selected(request('status') === $status)>{{ $status }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <button class="btn btn-outline-light w-100">Filtrar</button>
        </div>
        <div class="col-md-2 ms-auto">
            <a class="btn btn-systex w-100" href="{{ route('master.viagens.create') }}">Nova Viagem</a>
        </div>
    </form>
    <div class="table-responsive">
        <table class="table table-dark table-striped">
            <thead><tr><th>ID</th><th>Cliente</th><th>Origem</th><th>Destino</th><th>Prevista</th><th>Status</th><th></th></tr></thead>
            <tbody>
            @forelse($viagens as $viagem)
                <tr>
                    <td>{{ $viagem->id }}</td>
                    <td>{{ $viagem->cliente->nome_fantasia ?: $viagem->cliente->razao_social }}</td>
                    <td>{{ $viagem->origem }}</td>
                    <td>{{ $viagem->destino }}</td>
                    <td>{{ optional($viagem->data_prevista)->format('d/m/Y H:i') }}</td>
                    <td>{{ $viagem->status }}</td>
                    <td><a class="btn btn-outline-light btn-sm" href="{{ route('master.viagens.show', $viagem->id) }}">Ver</a></td>
                </tr>
            @empty
                <tr><td colspan="7">Sem viagens.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    {{ $viagens->links() }}
</div>
@endsection

