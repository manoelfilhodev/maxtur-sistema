@extends('layouts.app')

@section('page-heading')
    <div class="d-flex align-items-start justify-content-between flex-wrap gap-2">
        <div>
            <h3 class="fw-bold mb-1 text-white">Clientes</h3>
            <div class="text-muted" style="color: rgba(255,255,255,.65) !important;">
                Cadastro e controle de clientes
            </div>
        </div>

        <div class="d-flex gap-2">
            <a href="{{ route('painel.clientes.create') }}" class="btn btn-systex btn-sm">
                <i class="bi bi-plus-circle me-1"></i> Novo cliente
            </a>
        </div>
    </div>
@endsection

@section('content')
<style>
/* 🔥 NEUTRALIZA O BOOTSTRAP PARA ESSA TELA */
.table-systex-grid{
  --bs-table-bg: transparent !important;
  --bs-table-accent-bg: transparent !important;
  --bs-table-striped-bg: transparent !important;
  --bs-table-hover-bg: transparent !important;
  color: rgba(255,255,255,.9);
}
.table-systex-grid > :not(caption) > * > *{
  background-color: transparent !important;
  box-shadow: none !important;
}

/* =========================
   CARD / CONTAINER (GLASS)
========================== */
.systex-clientes-wrap .dash-card{
    background: rgba(18, 18, 20, .72) !important;
    border: 1px solid rgba(255, 255, 255, .08) !important;
    border-radius: 16px !important;
    box-shadow: 0 16px 45px rgba(0, 0, 0, .35);
    color: #fff;
}

/* Blindagem anti-branco */
.systex-clientes-wrap .table,
.systex-clientes-wrap .table-responsive,
.systex-clientes-wrap .table-responsive *{
    background-color: transparent !important;
}

.systex-clientes-wrap .table-shell{
    border-radius: 16px;
    overflow: hidden;
    border: 1px solid rgba(255,255,255,.08);
    background: rgba(12,12,14,.72) !important;
}

/* =========================
   TABELA DARK (padrao users)
========================== */
.systex-clientes-wrap table.table-systex-grid{
    width: 100%;
    margin: 0 !important;
    border-collapse: separate !important;
    border-spacing: 0 !important;
    background: rgba(12,12,14,.72) !important;
}

.systex-clientes-wrap table.table-systex-grid thead th{
    background: rgba(255,255,255,.04) !important;
    color: rgba(255,255,255,.78) !important;
    border-bottom: 1px solid rgba(255,255,255,.08) !important;
    border-top: 0 !important;
    font-weight: 800;
    font-size: 12px;
    letter-spacing: .3px;
    text-transform: uppercase;
    white-space: nowrap;
    padding: 12px 12px !important;
}

.systex-clientes-wrap table.table-systex-grid tbody{
    background: rgba(12,12,14,.72) !important;
}
.systex-clientes-wrap table.table-systex-grid tbody tr{
    background: rgba(18,18,20,.58) !important;
}
.systex-clientes-wrap table.table-systex-grid tbody tr:nth-child(even){
    background: rgba(18,18,20,.46) !important;
}
.systex-clientes-wrap table.table-systex-grid tbody td{
    background: transparent !important;
    color: rgba(255,255,255,.88) !important;
    border-top: 1px solid rgba(255,255,255,.06) !important;
    border-left: 0 !important;
    border-right: 0 !important;
    border-bottom: 0 !important;
    padding: 14px 12px !important;
    vertical-align: middle;
}
.systex-clientes-wrap table.table-systex-grid tbody tr:hover{
    background: rgba(255,255,255,.05) !important;
}
.systex-clientes-wrap table.table-systex-grid tbody tr:hover td{
    background: transparent !important;
}

/* Textos secundários */
.systex-clientes-wrap .muted{ color: rgba(255,255,255,.64) !important; }

/* =========================
   FILTROS (dark)
========================== */
.systex-clientes-wrap .filter-label{
    font-size: 12px;
    font-weight: 800;
    letter-spacing: .2px;
    text-transform: uppercase;
    color: rgba(255,255,255,.60);
    margin-bottom: 6px;
}
.systex-clientes-wrap .form-control,
.systex-clientes-wrap .form-select{
    background: rgba(255,255,255,.06) !important;
    border: 1px solid rgba(255,255,255,.10) !important;
    color: rgba(255,255,255,.90) !important;
    border-radius: 12px !important;
    padding: 10px 12px !important;
}
.systex-clientes-wrap .form-control::placeholder{
    color: rgba(255,255,255,.45) !important;
}
.systex-clientes-wrap .form-control:focus,
.systex-clientes-wrap .form-select:focus{
    box-shadow: 0 0 0 .2rem rgba(255,42,42,.12) !important;
    border-color: rgba(255,42,42,.35) !important;
}

/* Botões pequenos e alinhados */
.systex-clientes-wrap .btn-outline-light{
    border-color: rgba(255,255,255,.18) !important;
    color: rgba(255,255,255,.86) !important;
    background: rgba(255,255,255,.04) !important;
    border-radius: 12px !important;
}
.systex-clientes-wrap .btn-outline-light:hover{
    background: rgba(255,255,255,.08) !important;
}

/* Chip status */
.systex-clientes-wrap .chip{
    display:inline-flex;
    align-items:center;
    gap:8px;
    padding: 6px 10px;
    border-radius: 999px;
    font-weight: 800;
    font-size: 12px;
    border: 1px solid rgba(255,255,255,.10);
}
.systex-clientes-wrap .chip-ativo{
    background: rgba(34,197,94,.12) !important;
    border-color: rgba(34,197,94,.22) !important;
    color: #c8ffd8 !important;
}
.systex-clientes-wrap .chip-inativo{
    background: rgba(148,163,184,.12) !important;
    border-color: rgba(148,163,184,.22) !important;
    color: rgba(255,255,255,.75) !important;
}

/* Botões ícone (igual users) */
.systex-clientes-wrap .btn-icon{
    width: 34px;
    height: 34px;
    border-radius: 12px;
    display: inline-grid;
    place-items: center;
    padding: 0 !important;
}
.systex-clientes-wrap .btn-icon.btn-outline-light{
    border-color: rgba(255,255,255,.16) !important;
    color: rgba(255,255,255,.86) !important;
    background: rgba(255,255,255,.04) !important;
}
.systex-clientes-wrap .btn-icon.btn-outline-light:hover{
    background: rgba(255,255,255,.08) !important;
}
.systex-clientes-wrap .btn-icon.btn-danger{
    background: rgba(255,77,79,.12) !important;
    border: 1px solid rgba(255,77,79,.25) !important;
    color: #ffd0d0 !important;
}
.systex-clientes-wrap .btn-icon.btn-danger:hover{
    background: rgba(255,77,79,.18) !important;
}

/* Paginação no dark */
.systex-clientes-wrap .pagination .page-link{
    background: rgba(255,255,255,.04) !important;
    border: 1px solid rgba(255,255,255,.10) !important;
    color: rgba(255,255,255,.80) !important;
}
.systex-clientes-wrap .pagination .page-item.active .page-link{
    background: rgba(255,42,42,.18) !important;
    border-color: rgba(255,42,42,.35) !important;
    color: #fff !important;
}
</style>

<div class="container-fluid px-4 systex-clientes-wrap">

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center" role="alert">
            <i class="bi bi-check-circle-fill fs-5 me-2"></i>
            <span>{{ session('success') }}</span>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="dash-card p-3">

        {{-- Topo igual Users --}}
        <div class="d-flex align-items-center justify-content-between mb-2 flex-wrap gap-2">
            <div class="muted small">
                Total: <b class="text-white">{{ $clientes->total() }}</b> cliente(s)
            </div>
            <div class="muted small">
                Dica: use a busca para achar por nome, documento ou e-mail
            </div>
        </div>

        {{-- Filtros (dark) --}}
        <form method="GET" class="row g-2 align-items-end mb-3">
            <div class="col-lg-6">
                <div class="filter-label">Busca</div>
                <input type="text" name="busca" value="{{ request('busca') }}"
                       class="form-control"
                       placeholder="Nome, fantasia, documento ou e-mail">
            </div>

            <div class="col-lg-3">
                <div class="filter-label">Status</div>
                <select name="status" class="form-select">
                    <option value="">Todos</option>
                    <option value="ativo" {{ request('status')==='ativo'?'selected':'' }}>Ativo</option>
                    <option value="inativo" {{ request('status')==='inativo'?'selected':'' }}>Inativo</option>
                </select>
            </div>

            <div class="col-lg-3 d-flex gap-2">
                <button class="btn btn-systex w-100">
                    <i class="bi bi-funnel me-1"></i> Filtrar
                </button>
                <a href="{{ route('painel.clientes.index') }}" class="btn btn-outline-light w-100">
                    Limpar
                </a>
            </div>
        </form>

        <div class="table-responsive table-shell">
            <table class="table table-hover mb-0 table-systex-grid">
                <thead>
                    <tr>
                        <th>Cliente</th>
                        <th>Documento</th>
                        <th>Contato</th>
                        <th>Cidade/UF</th>
                        <th>Status</th>
                        <th width="150" class="text-center">Ações</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($clientes as $c)
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $c->razao_social }}</div>
                                <div class="muted small">{{ $c->nome_fantasia ?: '—' }}</div>
                            </td>

                            <td class="muted">
                                {{ $c->documento ?: '—' }}
                            </td>

                            <td>
                                <div class="muted small">{{ $c->email ?: '—' }}</div>
                                <div class="muted small">{{ $c->whatsapp ?: ($c->telefone ?: '—') }}</div>
                            </td>

                            <td class="muted">
                                {{ $c->cidade ?: '—' }}{{ $c->uf ? '/'.$c->uf : '' }}
                            </td>

                            <td>
                                @if($c->ativo)
                                    <span class="chip chip-ativo">
                                        <i class="bi bi-check-circle"></i> ATIVO
                                    </span>
                                @else
                                    <span class="chip chip-inativo">
                                        <i class="bi bi-slash-circle"></i> INATIVO
                                    </span>
                                @endif
                            </td>

                            <td class="text-center">
                                <div class="d-flex justify-content-center gap-2">
                                    <a href="{{ route('painel.clientes.show', $c->id) }}"
                                       class="btn btn-icon btn-outline-light"
                                       data-bs-toggle="tooltip" title="Ver">
                                        <i class="bi bi-eye"></i>
                                    </a>

                                    <a href="{{ route('painel.clientes.edit', $c->id) }}"
                                       class="btn btn-icon btn-outline-light"
                                       data-bs-toggle="tooltip" title="Editar">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>

                                    <form method="POST" action="{{ route('painel.clientes.toggle', $c->id) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button class="btn btn-icon btn-outline-light"
                                                data-bs-toggle="tooltip"
                                                title="{{ $c->ativo ? 'Inativar' : 'Ativar' }}">
                                            <i class="bi {{ $c->ativo ? 'bi-toggle-on' : 'bi-toggle-off' }}"></i>
                                        </button>
                                    </form>

                                    <form method="POST"
                                          action="{{ route('painel.clientes.destroy', $c->id) }}"
                                          onsubmit="return confirm('Remover este cliente? (Fica em lixeira/soft delete)');">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-icon btn-danger"
                                                data-bs-toggle="tooltip" title="Excluir">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center muted py-4">
                                Nenhum cliente encontrado.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="d-flex justify-content-end mt-3">
            {{ $clientes->links() }}
        </div>

    </div>
</div>
@endsection
