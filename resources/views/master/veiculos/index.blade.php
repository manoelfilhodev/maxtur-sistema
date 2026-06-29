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
        <form method="GET" class="sx-filter row g-2 align-items-end">
            <div class="col-md-4 col-lg-3">
                <label class="sx-label" for="filtro-manutencao">Manutenção</label>
                <select class="form-select" id="filtro-manutencao" name="manutencao">
                    <option value="">Todos os veículos</option>
                    <option value="atencao" @selected(request('manutencao') === 'atencao')>Exige atenção</option>
                    <option value="cadastrada" @selected(request('manutencao') === 'cadastrada')>Com manutenção cadastrada</option>
                    <option value="sem_registro" @selected(request('manutencao') === 'sem_registro')>Sem manutenção cadastrada</option>
                </select>
            </div>
            <div class="col-md-4 col-lg-3 d-flex gap-2">
                <button class="btn btn-systex flex-fill"><i class="bi bi-funnel"></i> Filtrar</button>
                <a class="btn btn-outline-light" href="{{ route('master.veiculos.index') }}">Limpar</a>
            </div>
        </form>
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
                            <th>Tipo</th>
                            <th>Capacidade</th>
                            <th>Status</th>
                            <th>Manutenção</th>
                            <th class="text-end">Acoes</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($veiculos as $veiculo)
                        <tr>
                            <td class="fw-semibold">{{ $veiculo->placa }}</td>
                            <td>{{ $veiculo->modelo }}</td>
                            <td>{{ $veiculo->tipo === 'parceiro' ? 'Parceiro' : 'Próprio' }}</td>
                            <td>{{ $veiculo->capacidade_passageiros }} passageiros</td>
                            <td>
                                @include('partials.panel.status-badge', ['status' => $veiculo->status_operacional ?: 'ativo'])
                            </td>
                            <td>
                                @if($veiculo->manutencoes_atencao_count > 0)
                                    <a href="{{ route('master.veiculos.show', $veiculo) }}" title="Abrir manutenção">
                                        <span class="sx-badge sx-badge-danger"><i class="bi bi-exclamation-octagon"></i> {{ $veiculo->manutencoes_atencao_count }} em atenção</span>
                                    </a>
                                @elseif($veiculo->manutencoes_count > 0)
                                    <span class="sx-badge sx-badge-success"><i class="bi bi-check-circle"></i> Em dia</span>
                                @else
                                    <span class="sx-badge sx-badge-muted"><i class="bi bi-dash-circle"></i> Sem registro</span>
                                @endif
                            </td>
                            <td>
                                <div class="sx-actions">
                                    <a class="btn btn-icon btn-outline-light" href="{{ route('master.veiculos.show', $veiculo->id) }}" data-bs-toggle="tooltip" title="Visualizar">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a class="btn btn-icon btn-outline-light" href="{{ route('master.veiculos.edit', $veiculo) }}" data-bs-toggle="tooltip" title="Editar"><i class="bi bi-pencil-square"></i></a>
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
