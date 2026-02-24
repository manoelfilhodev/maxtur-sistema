@extends('layouts.app')

@section('page-heading')
<div class="d-flex align-items-start justify-content-between flex-wrap gap-2">
  <div>
    <h3 class="fw-bold mb-1 text-white">Relatório de Batidas</h3>
    <div class="text-muted" style="color: rgba(255,255,255,.65) !important;">
      Exportação por funcionário (Excel com abas + PDF)
    </div>
  </div>

  <div class="d-flex gap-2">
    <button form="filtroRelBatidas" formaction="{{ route('painel.relatorios.batidas.excel') }}"

            class="btn btn-outline-light btn-sm">
      <i class="bi bi-file-earmark-excel me-1"></i> Excel
    </button>

    <button form="filtroRelBatidas" formaction="{{ route('painel.relatorios.batidas.pdf') }}"

            class="btn btn-outline-light btn-sm">
      <i class="bi bi-file-earmark-pdf me-1"></i> PDF
    </button>
  </div>
</div>
@endsection

@section('content')
<div class="card border-0 shadow-sm"
     style="background: rgba(255,255,255,.06); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,.10);">
  <div class="card-body">
    <form id="filtroRelBatidas" method="GET" class="row g-3">
      <div class="col-md-3">
        <label class="form-label text-white">Início</label>
        <input type="date" name="inicio" class="form-control"
               value="{{ request('inicio', now()->startOfMonth()->toDateString()) }}" required>
      </div>

      <div class="col-md-3">
        <label class="form-label text-white">Fim</label>
        <input type="date" name="fim" class="form-control"
               value="{{ request('fim', now()->toDateString()) }}" required>
      </div>

      <div class="col-md-6">
        <label class="form-label text-white">Funcionário (opcional)</label>
        <select name="usuario_id" class="form-select">
          <option value="">Todos</option>
          @foreach($usuarios as $u)
            <option value="{{ $u->id }}" @selected(request('usuario_id') == $u->id)>
              {{ $u->name }} {{ $u->cpf ? ' • '.$u->cpf : '' }}
            </option>
          @endforeach
        </select>
      </div>

      <div class="col-12">
        <div class="text-muted" style="color: rgba(255,255,255,.65) !important;">
          Dica: se não selecionar funcionário, o Excel virá com <strong>1 aba por colaborador</strong>.
        </div>
      </div>
    </form>
  </div>
</div>
@endsection
