@extends('layouts.app')

@section('page-heading')
    @include('partials.panel.page-header', [
        'title' => 'Checklists',
        'subtitle' => 'Inspecoes vinculadas aos veiculos e motoristas',
    ])
@endsection

@section('content')
<div class="sx-container">
    <div class="sx-card">
        <div class="sx-card-header">
            <div>
                <h5 class="sx-card-title">Historico de checklists</h5>
                <div class="sx-muted small">Acompanhe status, resultado e periodo de execucao</div>
            </div>
        </div>

        @if($checklists->count())
            <div class="table-responsive sx-table-shell">
                <table class="table table-hover table-systex-grid">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Veiculo</th>
                            <th>Motorista</th>
                            <th>Status</th>
                            <th>Resultado</th>
                            <th>Inicio</th>
                            <th>Fim</th>
                            <th class="text-end">Acoes</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($checklists as $checklist)
                        <tr>
                            <td class="fw-semibold">#{{ $checklist->id }}</td>
                            <td>{{ $checklist->veiculo->placa ?? '-' }}</td>
                            <td>{{ $checklist->motorista->name ?? '-' }}</td>
                            <td>@include('partials.panel.status-badge', ['status' => $checklist->status])</td>
                            <td>@include('partials.panel.status-badge', ['status' => $checklist->resultado ?? 'pendente'])</td>
                            <td>{{ optional($checklist->started_at)->format('d/m/Y H:i') }}</td>
                            <td>{{ optional($checklist->finished_at)->format('d/m/Y H:i') }}</td>
                            <td>
                                <div class="sx-actions">
                                    <a class="btn btn-icon btn-outline-light" href="{{ route('painel.operador.checklists.show', $checklist->id) }}" data-bs-toggle="tooltip" title="Detalhes">
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
                'title' => 'Nenhum checklist encontrado',
                'message' => 'Os checklists iniciados pelo aplicativo aparecerao aqui para auditoria operacional.',
                'icon' => 'bi bi-clipboard-check',
            ])
        @endif

        <div class="d-flex justify-content-end mt-3">
            {{ $checklists->links() }}
        </div>
    </div>
</div>
@endsection
