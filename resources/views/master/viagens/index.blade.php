@extends('layouts.app')

@section('page-heading')
    @include('partials.panel.page-header', [
        'title' => 'Viagens',
        'subtitle' => 'Cadastro e acompanhamento das viagens da operacao',
        'actionRoute' => route('master.viagens.create'),
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
                    @foreach(['programada','em_andamento','realizada','cancelada','atrasada'] as $status)
                        <option value="{{ $status }}" @selected(request('status') === $status)>{{ str_replace('_', ' ', $status) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 col-lg-2">
                <label class="sx-label">Tipo/período</label>
                <select name="tipo_periodo" class="form-select"><option value="">Todos</option>@foreach(['diario'=>'Diário','mensal'=>'Mensal','esporadico'=>'Esporádico'] as $valor=>$rotulo)<option value="{{ $valor }}" @selected(request('tipo_periodo') === $valor)>{{ $rotulo }}</option>@endforeach</select>
            </div>
            <div class="col-md-3 col-lg-2">
                <button class="btn btn-systex w-100"><i class="bi bi-funnel"></i> Filtrar</button>
            </div>
        </form>

        @if($viagens->count())
            <div class="table-responsive sx-table-shell">
                <table class="table table-hover table-systex-grid">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Cliente</th>
                            <th>Origem</th>
                            <th>Destino</th>
                            <th>Prevista</th>
                            <th>Tipo/período</th>
                            <th>Natureza</th>
                            <th>Status</th>
                            <th class="text-end">Acoes</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($viagens as $viagem)
                        <tr>
                            <td class="fw-semibold">#{{ $viagem->id }}</td>
                            <td>{{ $viagem->cliente->nome_fantasia ?: $viagem->cliente->razao_social }}</td>
                            <td>{{ $viagem->origem }}</td>
                            <td>{{ $viagem->destino }}</td>
                            <td>{{ optional($viagem->data_prevista)->format('d/m/Y H:i') }}</td>
                            <td>{{ ['diario'=>'Diário','mensal'=>'Mensal','esporadico'=>'Esporádico'][$viagem->tipo_periodo] ?? $viagem->tipo_periodo }}</td>
                            <td><span class="badge bg-{{ $viagem->natureza === 'extra' ? 'warning' : 'info' }}">{{ $viagem->natureza === 'extra' ? 'Extra' : 'Programada' }}</span></td>
                            <td>@include('partials.panel.status-badge', ['status' => $viagem->status])</td>
                            <td>
                                <div class="sx-actions">
                                    <a class="btn btn-icon btn-outline-light" href="{{ route('master.viagens.show', $viagem->id) }}" data-bs-toggle="tooltip" title="Visualizar">
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
                'message' => 'Cadastre a primeira viagem para iniciar o acompanhamento operacional.',
                'actionRoute' => route('master.viagens.create'),
                'actionLabel' => 'Nova viagem',
                'icon' => 'bi bi-sign-turn-right',
            ])
        @endif

        <div class="d-flex justify-content-end mt-3">
            {{ $viagens->links() }}
        </div>
    </div>
</div>
@endsection
