@extends('layouts.app')

@section('page-heading')
    @include('partials.panel.page-header', [
        'title' => 'Veiculos',
        'subtitle' => 'Cadastro e controle da frota operacional',
        'actionRoute' => route('master.veiculos.create'),
        'actionLabel' => 'Novo veiculo',
    ])
@endsection

@section('content')
<div class="sx-container">
    <div class="sx-card">
        <div class="sx-card-header">
            <div>
                <h5 class="sx-card-title">Frota cadastrada</h5>
                <div class="sx-muted small">Total: <b class="text-white">{{ $veiculos->total() }}</b> veiculo(s)</div>
            </div>
        </div>

        @if($veiculos->count())
            <div class="table-responsive sx-table-shell">
                <table class="table table-hover table-systex-grid">
                    <thead>
                        <tr>
                            <th>Placa</th>
                            <th>Modelo</th>
                            <th>Capacidade</th>
                            <th>Status</th>
                            <th class="text-end">Acoes</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($veiculos as $veiculo)
                        <tr>
                            <td class="fw-semibold">{{ $veiculo->placa }}</td>
                            <td>{{ $veiculo->modelo }}</td>
                            <td>{{ $veiculo->capacidade_passageiros }} passageiros</td>
                            <td>
                                @include('partials.panel.status-badge', ['status' => $veiculo->status_operacional ?: 'ativo'])
                            </td>
                            <td>
                                <div class="sx-actions">
                                    <a class="btn btn-icon btn-outline-light" href="{{ route('master.veiculos.show', $veiculo->id) }}" data-bs-toggle="tooltip" title="Visualizar">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        @else
            @include('partials.panel.empty-state', [
                'title' => 'Nenhum veiculo encontrado',
                'message' => 'Cadastre o primeiro veiculo para iniciar o controle da frota operacional.',
                'actionRoute' => route('master.veiculos.create'),
                'actionLabel' => 'Novo veiculo',
                'icon' => 'bi bi-truck',
            ])
        @endif

        <div class="d-flex justify-content-end mt-3">
            {{ $veiculos->links() }}
        </div>
    </div>
</div>
@endsection
