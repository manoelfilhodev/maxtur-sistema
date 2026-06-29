@extends('layouts.app')

@section('page-heading')
    @include('partials.panel.page-header', [
        'title' => 'Motoristas',
        'subtitle' => 'Usuários com perfil de motorista disponíveis para atribuição em viagens',
        'actionRoute' => route('master.motoristas.create'),
        'actionLabel' => 'Novo motorista',
    ])
@endsection

@section('content')
<div class="sx-container">
    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="sx-card">
        <div class="sx-card-header">
            <div>
                <h5 class="sx-card-title">Motoristas operacionais</h5>
                <div class="sx-muted small">Total: <b class="text-white">{{ $motoristas->total() }}</b> motorista(s)</div>
            </div>
            <span class="sx-badge sx-badge-info"><i class="bi bi-person-check"></i> Perfil MOTORISTA</span>
        </div>

        @if($motoristas->count())
            <div class="table-responsive sx-table-shell">
                <table class="table table-hover table-systex-grid">
                    <thead>
                        <tr>
                            <th>Motorista</th>
                            <th>E-mail</th>
                            <th>Telefone</th>
                            <th>Documento/CNH</th>
                            <th>Vencimento CNH</th>
                            <th>Status</th>
                            <th class="text-end">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($motoristas as $motorista)
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $motorista->name }}</div>
                                <div class="sx-muted small">{{ strtoupper($motorista->role ?? 'MOTORISTA') }}</div>
                            </td>
                            <td class="sx-muted">{{ $motorista->email }}</td>
                            <td class="sx-muted">{{ $motorista->telefone ?: '-' }}</td>
                            <td class="sx-muted">{{ $motorista->cpf ?: '-' }}</td>
                            <td>
                                @if(!$motorista->cnh_vencimento)<span class="sx-muted">-</span>
                                @else
                                    @php($diasCnh = now()->startOfDay()->diffInDays($motorista->cnh_vencimento, false))
                                    <span class="badge bg-{{ $diasCnh < 0 ? 'danger' : ($diasCnh <= 30 ? 'warning' : 'success') }}">{{ $motorista->cnh_vencimento->format('d/m/Y') }}</span>
                                @endif
                            </td>
                            <td>
                                @include('partials.panel.status-badge', ['status' => $motorista->ativo ? 'ativo' : 'inativo'])
                            </td>
                            <td>
                                <div class="sx-actions">
                                    <a class="btn btn-icon btn-outline-light" href="{{ route('master.motoristas.show', $motorista->id) }}" data-bs-toggle="tooltip" title="Visualizar">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a class="btn btn-icon btn-outline-light" href="{{ route('master.motoristas.edit', $motorista->id) }}" data-bs-toggle="tooltip" title="Editar">
                                        <i class="bi bi-pencil-square"></i>
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
                'title' => 'Nenhum motorista encontrado',
                'message' => 'Cadastre o primeiro usuário motorista para habilitar a atribuição de viagens.',
                'actionRoute' => route('master.motoristas.create'),
                'actionLabel' => 'Novo motorista',
                'icon' => 'bi bi-person-badge',
            ])
        @endif

        <div class="d-flex justify-content-end mt-3">
            {{ $motoristas->links() }}
        </div>
    </div>
</div>
@endsection
