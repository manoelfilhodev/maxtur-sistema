@extends('layouts.app')

@section('page-heading')
<h3 class="text-white mb-0">Master - Veiculos</h3>
@endsection

@section('content')
<div class="dash-card p-3">
    <div class="d-flex justify-content-end mb-3">
        <a class="btn btn-systex btn-sm" href="{{ route('master.veiculos.create') }}">Novo Veiculo</a>
    </div>
    <div class="table-responsive">
        <table class="table table-dark table-striped">
            <thead><tr><th>Placa</th><th>Modelo</th><th>Capacidade</th><th>Status</th><th></th></tr></thead>
            <tbody>
            @forelse($veiculos as $veiculo)
                <tr>
                    <td>{{ $veiculo->placa }}</td>
                    <td>{{ $veiculo->modelo }}</td>
                    <td>{{ $veiculo->capacidade_passageiros }}</td>
                    <td>{{ $veiculo->status_operacional }}</td>
                    <td><a class="btn btn-outline-light btn-sm" href="{{ route('master.veiculos.show', $veiculo->id) }}">Ver</a></td>
                </tr>
            @empty
                <tr><td colspan="5">Sem veiculos.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    {{ $veiculos->links() }}
</div>
@endsection

