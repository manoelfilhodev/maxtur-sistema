@extends('layouts.app')

@section('page-heading')
    @include('partials.panel.page-header', [
        'title' => 'Checklists de veículos',
        'subtitle' => 'Inspeções registradas e resultados da frota',
        'actionRoute' => route('checklists.create'),
        'actionLabel' => 'Novo checklist',
    ])
@endsection

@section('content')
<div class="sx-container">
    @include('partials.panel.flash-messages')
    <div class="sx-card">
        @if($checklists->count())
            <div class="table-responsive sx-table-shell">
                <table class="table table-hover table-systex-grid align-middle">
                    <thead><tr><th>ID</th><th>Data</th><th>Veículo</th><th>Motorista</th><th>Status</th><th class="text-end">Ações</th></tr></thead>
                    <tbody>
                    @foreach($checklists as $checklist)
                        <tr>
                            <td class="fw-bold">#{{ $checklist->id }}</td>
                            <td>{{ $checklist->data?->format('d/m/Y') ?: '-' }}</td>
                            <td>{{ $checklist->placa ?? ($checklist->veiculo_identificacao ?? '-') }}</td>
                            <td>{{ $checklist->motorista_nome ?? '-' }}</td>
                            <td>@include('partials.panel.status-badge', ['status' => $checklist->status])</td>
                            <td><div class="sx-actions"><a href="{{ route('checklists.show', $checklist) }}" class="btn btn-icon btn-outline-light" title="Visualizar checklist" aria-label="Visualizar checklist #{{ $checklist->id }}"><i class="bi bi-eye" aria-hidden="true"></i></a></div></td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-end mt-3">{{ $checklists->links() }}</div>
        @else
            @include('partials.panel.empty-state', [
                'title' => 'Nenhum checklist registrado',
                'message' => 'Crie o primeiro checklist para iniciar o histórico de inspeções da frota.',
                'actionRoute' => route('checklists.create'),
                'actionLabel' => 'Novo checklist',
                'icon' => 'bi bi-clipboard2-check',
            ])
        @endif
    </div>
</div>
@endsection
