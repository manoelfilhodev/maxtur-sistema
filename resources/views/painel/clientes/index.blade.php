@extends('layouts.app')

@section('page-heading')
    @include('partials.panel.page-header', [
        'title' => 'Clientes',
        'subtitle' => 'Cadastro e controle de clientes da operacao',
        'actionRoute' => route('painel.clientes.create'),
        'actionLabel' => 'Novo cliente',
    ])
@endsection

@section('content')
<div class="sx-container">
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center" role="alert">
            <i class="bi bi-check-circle-fill fs-5 me-2"></i>
            <span>{{ session('success') }}</span>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="sx-card">
        <div class="sx-card-header">
            <div>
                <h5 class="sx-card-title">Base de clientes</h5>
                <div class="sx-muted small">Total: <b class="text-white">{{ $clientes->total() }}</b> cliente(s)</div>
            </div>
            <div class="sx-muted small">Busque por nome, documento ou e-mail</div>
        </div>

        <form method="GET" class="sx-filter row g-2 align-items-end">
            <div class="col-lg-6">
                <label class="sx-label">Busca</label>
                <input type="text" name="busca" value="{{ request('busca') }}" class="form-control" placeholder="Nome, fantasia, documento ou e-mail">
            </div>
            <div class="col-lg-3">
                <label class="sx-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">Todos</option>
                    <option value="ativo" {{ request('status')==='ativo'?'selected':'' }}>Ativo</option>
                    <option value="inativo" {{ request('status')==='inativo'?'selected':'' }}>Inativo</option>
                </select>
            </div>
            <div class="col-lg-3 d-flex gap-2">
                <button class="btn btn-systex w-100"><i class="bi bi-funnel"></i> Filtrar</button>
                <a href="{{ route('painel.clientes.index') }}" class="btn btn-outline-light w-100">Limpar</a>
            </div>
        </form>

        @if($clientes->count())
            <div class="table-responsive sx-table-shell">
                <table class="table table-hover table-systex-grid">
                    <thead>
                        <tr>
                            <th>Cliente</th>
                            <th>Documento</th>
                            <th>Contato</th>
                            <th>Cidade/UF</th>
                            <th>Status</th>
                            <th class="text-end">Acoes</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($clientes as $c)
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $c->razao_social }}</div>
                                <div class="sx-muted small">{{ $c->nome_fantasia ?: '-' }}</div>
                            </td>
                            <td class="sx-muted">{{ $c->documento ?: '-' }}</td>
                            <td>
                                <div class="sx-muted small">{{ $c->email ?: '-' }}</div>
                                <div class="sx-muted small">{{ $c->whatsapp ?: ($c->telefone ?: '-') }}</div>
                            </td>
                            <td class="sx-muted">{{ $c->cidade ?: '-' }}{{ $c->uf ? '/'.$c->uf : '' }}</td>
                            <td>
                                @include('partials.panel.status-badge', ['status' => $c->ativo ? 'ativo' : 'inativo'])
                            </td>
                            <td>
                                <div class="sx-actions">
                                    <a href="{{ route('painel.clientes.show', $c->id) }}" class="btn btn-icon btn-outline-light" data-bs-toggle="tooltip" title="Visualizar">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('painel.clientes.edit', $c->id) }}" class="btn btn-icon btn-outline-light" data-bs-toggle="tooltip" title="Editar">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                    <form method="POST" action="{{ route('painel.clientes.toggle', $c->id) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button class="btn btn-icon btn-outline-light" data-bs-toggle="tooltip" title="{{ $c->ativo ? 'Inativar' : 'Ativar' }}">
                                            <i class="bi {{ $c->ativo ? 'bi-toggle-on' : 'bi-toggle-off' }}"></i>
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('painel.clientes.destroy', $c->id) }}" onsubmit="return confirm('Remover este cliente? (Fica em lixeira/soft delete)');">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-icon btn-danger" data-bs-toggle="tooltip" title="Excluir">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        @else
            @include('partials.panel.empty-state', [
                'title' => 'Nenhum cliente encontrado',
                'message' => 'Cadastre o primeiro cliente ou ajuste os filtros para localizar registros existentes.',
                'actionRoute' => route('painel.clientes.create'),
                'actionLabel' => 'Novo cliente',
                'icon' => 'bi bi-building',
            ])
        @endif

        <div class="d-flex justify-content-end mt-3">
            {{ $clientes->links() }}
        </div>
    </div>
</div>
@endsection
