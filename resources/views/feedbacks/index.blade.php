@extends('layouts.app')

@section('page-heading')
<div class="d-flex align-items-start justify-content-between flex-wrap gap-2">
    <div>
        <h3 class="fw-bold mb-1 text-white">Feedbacks de funcionários</h3>
        <div class="text-muted" style="color: rgba(255,255,255,.65) !important;">
            Sugestões e críticas enviadas pelo aplicativo.
        </div>
    </div>
</div>
@endsection

@section('content')
<div class="dash-card p-3">
    <div class="table-responsive">
        <table class="table table-dark table-hover align-middle">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Tipo</th>
                    <th>Funcionário</th>
                    <th>Status</th>
                    <th>Enviado em</th>
                    <th class="text-end">Ação</th>
                </tr>
            </thead>
            <tbody>
            @forelse($feedbacks as $item)
                <tr>
                    <td>#{{ $item->id }}</td>
                    <td>
                        @if($item->tipo === 'critica')
                            <span class="badge bg-danger">Crítica</span>
                        @else
                            <span class="badge bg-info">Sugestão</span>
                        @endif
                    </td>
                    <td>
                        <div class="fw-semibold">{{ $item->funcionario->name ?? '-' }}</div>
                        <div class="small text-muted">{{ $item->funcionario->email ?? '-' }}</div>
                    </td>
                    <td>
                        @if($item->status === 'novo')
                            <span class="badge bg-warning text-dark">Novo</span>
                        @elseif($item->status === 'lido')
                            <span class="badge bg-success">Lido</span>
                        @else
                            <span class="badge bg-secondary">{{ ucfirst($item->status) }}</span>
                        @endif
                    </td>
                    <td>{{ optional($item->created_at)->format('d/m/Y H:i') }}</td>
                    <td class="text-end">
                        @if(auth()->user()->isMaster())
                            <a href="{{ route('painel.feedbacks.show', $item->id) }}" class="btn btn-outline-light btn-sm">Abrir</a>
                        @else
                            <a href="{{ route('tenant.feedbacks.show', $item->id) }}" class="btn btn-outline-light btn-sm">Abrir</a>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center text-muted py-4">Nenhum feedback encontrado.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-3 d-flex justify-content-end">
        {{ $feedbacks->links() }}
    </div>
</div>
@endsection
