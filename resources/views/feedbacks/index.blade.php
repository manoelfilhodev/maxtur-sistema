@extends('layouts.app')

@section('page-heading')
    @include('partials.panel.page-header', [
        'title' => 'Feedbacks',
        'subtitle' => 'Sugestoes e criticas enviadas pelo aplicativo',
    ])
@endsection

@section('content')
<div class="sx-container">
    <div class="sx-card">
        <div class="sx-card-header">
            <div>
                <h5 class="sx-card-title">Mensagens recebidas</h5>
                <div class="sx-muted small">Total: <b class="text-white">{{ $feedbacks->total() }}</b> feedback(s)</div>
            </div>
        </div>

        @if($feedbacks->count())
            <div class="table-responsive sx-table-shell">
                <table class="table table-hover table-systex-grid">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Tipo</th>
                            <th>Funcionario</th>
                            <th>Status</th>
                            <th>Enviado em</th>
                            <th class="text-end">Acoes</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($feedbacks as $item)
                        <tr>
                            <td class="fw-semibold">#{{ $item->id }}</td>
                            <td>
                                @include('partials.panel.status-badge', [
                                    'status' => $item->tipo,
                                    'label' => $item->tipo === 'critica' ? 'CRITICA' : 'SUGESTAO',
                                ])
                            </td>
                            <td>
                                <div class="fw-semibold">{{ $item->funcionario->name ?? '-' }}</div>
                                <div class="small sx-muted">{{ $item->funcionario->email ?? '-' }}</div>
                            </td>
                            <td>
                                @include('partials.panel.status-badge', ['status' => $item->status])
                            </td>
                            <td>{{ optional($item->created_at)->format('d/m/Y H:i') }}</td>
                            <td>
                                <div class="sx-actions">
                                    @if(auth()->user()->isMaster())
                                        <a href="{{ route('painel.feedbacks.show', $item->id) }}" class="btn btn-icon btn-outline-light" data-bs-toggle="tooltip" title="Abrir">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    @else
                                        <a href="{{ route('tenant.feedbacks.show', $item->id) }}" class="btn btn-icon btn-outline-light" data-bs-toggle="tooltip" title="Abrir">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        @else
            @include('partials.panel.empty-state', [
                'title' => 'Nenhum feedback encontrado',
                'message' => 'As mensagens enviadas pelo aplicativo aparecerao aqui para triagem e acompanhamento.',
                'icon' => 'bi bi-chat-left-text',
            ])
        @endif

        <div class="mt-3 d-flex justify-content-end">
            {{ $feedbacks->links() }}
        </div>
    </div>
</div>
@endsection
