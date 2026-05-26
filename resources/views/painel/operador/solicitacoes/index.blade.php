@extends('layouts.app')

@section('page-heading')
    @include('partials.panel.page-header', [
        'title' => 'Viagens',
        'subtitle' => 'Solicitacoes, acompanhamento e status da operacao',
        'actionRoute' => route('painel.operador.solicitacoes.create'),
        'actionLabel' => 'Nova viagem',
    ])
@endsection

@section('content')
<div class="sx-container">
    <div class="sx-card">
        <form method="GET" class="sx-filter row g-2 align-items-end">
            <div class="col-md-4 col-lg-3">
                <label class="sx-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">Todos os status</option>
                    @foreach($statusOptions as $status => $label)
                        <option value="{{ $status }}" @selected(request('status') === $status)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 col-lg-2">
                <button class="btn btn-systex w-100"><i class="bi bi-funnel"></i> Filtrar</button>
            </div>
        </form>

        @if($solicitacoes->count())
            <div class="table-responsive sx-table-shell">
                <table class="table table-hover table-systex-grid">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Cliente</th>
                            <th>Origem</th>
                            <th>Destino</th>
                            <th>Data/Hora</th>
                            <th>Veículo</th>
                            <th>Motorista</th>
                            <th>Status</th>
                            <th class="text-end">Acoes</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($solicitacoes as $solicitacao)
                        <tr>
                            <td class="fw-semibold">#{{ $solicitacao->id }}</td>
                            <td>{{ $solicitacao->cliente->nome_fantasia ?? $solicitacao->cliente->razao_social ?? '-' }}</td>
                            <td>{{ $solicitacao->origem }}</td>
                            <td>{{ $solicitacao->destino }}</td>
                            <td>{{ optional($solicitacao->data_hora)->format('d/m/Y H:i') }}</td>
                            @php($atribuicao = $solicitacao->atribuicoes->last())
                            <td>{{ $atribuicao?->veiculo?->placa ?? '-' }}</td>
                            <td>{{ $atribuicao?->motorista?->name ?? '-' }}</td>
                            <td>
                                @include('partials.panel.status-badge', ['status' => $solicitacao->status, 'label' => $solicitacao->statusLabel()])
                            </td>
                            <td>
                                <div class="sx-actions">
                                    <a class="btn btn-icon btn-outline-light" href="{{ route('painel.operador.solicitacoes.show', $solicitacao->id) }}" data-bs-toggle="tooltip" title="Detalhes">
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
                'title' => 'Nenhuma viagem encontrada',
                'message' => 'Crie uma nova viagem ou altere o filtro de status para visualizar solicitacoes existentes.',
                'actionRoute' => route('painel.operador.solicitacoes.create'),
                'actionLabel' => 'Nova viagem',
                'icon' => 'bi bi-sign-turn-right',
            ])
        @endif

        <div class="d-flex justify-content-end mt-3">
            {{ $solicitacoes->links() }}
        </div>
    </div>
</div>
@endsection
