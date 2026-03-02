@extends('layouts.app')

@section('page-heading')
<h3 class="text-white mb-0">Master - Motoristas</h3>
@endsection

@section('content')
<div class="dash-card p-3">
    <div class="d-flex justify-content-end mb-3">
        <a class="btn btn-systex btn-sm" href="{{ route('master.motoristas.create') }}">Novo Motorista</a>
    </div>
    <div class="table-responsive">
        <table class="table table-dark table-striped">
            <thead><tr><th>Nome</th><th>CNH</th><th>Telefone</th><th>Status</th><th></th></tr></thead>
            <tbody>
            @forelse($motoristas as $motorista)
                <tr>
                    <td>{{ $motorista->nome }}</td>
                    <td>{{ $motorista->cnh ?: '-' }}</td>
                    <td>{{ $motorista->telefone ?: '-' }}</td>
                    <td>{{ $motorista->ativo ? 'Ativo' : 'Inativo' }}</td>
                    <td><a class="btn btn-outline-light btn-sm" href="{{ route('master.motoristas.show', $motorista->id) }}">Ver</a></td>
                </tr>
            @empty
                <tr><td colspan="5">Sem motoristas.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    {{ $motoristas->links() }}
</div>
@endsection

