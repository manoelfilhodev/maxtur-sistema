@extends('layouts.app')

@section('page-heading')
<h3 class="text-white mb-0">Master - Clientes</h3>
@endsection

@section('content')
<div class="dash-card p-3">
    <div class="d-flex justify-content-between mb-3">
        <div class="text-muted">Total: {{ $clientes->total() }}</div>
        <a class="btn btn-systex btn-sm" href="{{ route('master.clientes.create') }}">Novo Cliente</a>
    </div>
    <div class="table-responsive">
        <table class="table table-dark table-striped">
            <thead><tr><th>ID</th><th>Razao Social</th><th>Fantasia</th><th>CNPJ</th><th>Status</th><th></th></tr></thead>
            <tbody>
            @forelse($clientes as $cliente)
                <tr>
                    <td>{{ $cliente->id }}</td>
                    <td>{{ $cliente->razao_social }}</td>
                    <td>{{ $cliente->nome_fantasia ?: '-' }}</td>
                    <td>{{ $cliente->cnpj ?: '-' }}</td>
                    <td>{{ $cliente->ativo ? 'Ativo' : 'Inativo' }}</td>
                    <td><a class="btn btn-outline-light btn-sm" href="{{ route('master.clientes.show', $cliente->id) }}">Ver</a></td>
                </tr>
            @empty
                <tr><td colspan="6">Sem clientes.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    {{ $clientes->links() }}
</div>
@endsection

