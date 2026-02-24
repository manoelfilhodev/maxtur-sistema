{{-- resources/views/painel/checklists/create.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="container-fluid">

    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
        <div>
            <h3 class="fw-bold text-white mb-1">Novo Checklist</h3>
            <div class="text-muted" style="color: rgba(255,255,255,.65) !important;">
                Marque OK/FALHA. Se for FALHA, foto é obrigatória.
            </div>
        </div>

        <a href="{{ route('checklists.index') }}" class="btn btn-sx-outline btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Voltar
        </a>
    </div>

    @if(session('error'))
        <div class="alert alert-danger d-flex align-items-center gap-2">
            <i class="bi bi-exclamation-triangle-fill"></i>
            <div>{{ session('error') }}</div>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            <div class="fw-bold mb-1">Corrija os campos:</div>
            <ul class="mb-0">
                @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('checklists.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="card sx-glass mb-3">
            <div class="card-body row g-3">

                <div class="col-md-3">
                    <label class="form-label text-white">Data *</label>
                    <input type="date" name="data" value="{{ old('data', date('Y-m-d')) }}" class="form-control sx-input" required>
                </div>

                <div class="col-md-3">
                    <label class="form-label text-white">Veículo (placa/modelo)</label>
                    <input type="text" name="veiculo_identificacao" value="{{ old('veiculo_identificacao') }}" class="form-control sx-input" maxlength="50">
                </div>

                <div class="col-md-3">
                    <label class="form-label text-white">Motorista</label>
                    <input type="text" name="motorista_nome" value="{{ old('motorista_nome') }}" class="form-control sx-input" maxlength="120">
                </div>

                <div class="col-md-3">
                    <label class="form-label text-white">Empresa fornecedora</label>
                    <input type="text" name="empresa_fornecedora" value="{{ old('empresa_fornecedora') }}" class="form-control sx-input" maxlength="120">
                </div>

                <div class="col-md-4">
                    <label class="form-label text-white">Inspecionado por</label>
                    <input type="text" name="inspecionado_por" value="{{ old('inspecionado_por') }}" class="form-control sx-input" maxlength="120">
                </div>

                <div class="col-md-4">
                    <label class="form-label text-white">Responsável</label>
                    <input type="text" name="responsavel_nome" value="{{ old('responsavel_nome') }}" class="form-control sx-input" maxlength="120">
                </div>

                <div class="col-md-4">
                    <label class="form-label text-white">Função</label>
                    <input type="text" name="responsavel_funcao" value="{{ old('responsavel_funcao') }}" class="form-control sx-input" maxlength="120">
                </div>

                <div class="col-12">
                    <label class="form-label text-white">Comentários do motorista</label>
                    <textarea name="comentarios_motorista" class="form-control sx-textarea" rows="2" placeholder="Opcional">{{ old('comentarios_motorista') }}</textarea>
                </div>

            </div>
        </div>

        <div class="card sx-glass">
            <div class="card-body">

                <div class="sx-table-wrap">
                    <table class="table sx-table table-hover align-middle">
                        <thead>
                            <tr>
                                <th style="width:70px;">#</th>
                                <th>Item</th>
                                <th style="width:240px;">Status</th>
                                <th>Observação</th>
                                <th style="width:280px;">Foto (se falha)</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach($itens as $item)
                            @php $oldStatus = old("itens.{$item->id}.status"); @endphp
                            <tr>
                                <td class="fw-bold">#{{ $item->codigo }}</td>
                                <td>{{ $item->titulo }}</td>

                                <td>
                                    <div class="d-flex gap-2 flex-wrap">
                                        <label class="sx-chip sx-chip--ok {{ $oldStatus==='ok' ? 'active' : '' }}">
                                            <input type="radio" name="itens[{{ $item->id }}][status]" value="ok" autocomplete="off" {{ $oldStatus==='ok' ? 'checked' : '' }}>
                                            OK
                                        </label>

                                        <label class="sx-chip sx-chip--falha {{ $oldStatus==='falha' ? 'active' : '' }}">
                                            <input type="radio" name="itens[{{ $item->id }}][status]" value="falha" autocomplete="off" {{ $oldStatus==='falha' ? 'checked' : '' }}>
                                            FALHA
                                        </label>
                                    </div>
                                </td>

                                <td>
                                    <input type="text" name="itens[{{ $item->id }}][observacao]"
                                           value="{{ old("itens.{$item->id}.observacao") }}"
                                           class="form-control sx-input"
                                           placeholder="Opcional">
                                </td>

                                <td>
                                    <input type="file" name="itens[{{ $item->id }}][foto]" class="form-control sx-file" accept="image/*">
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-end gap-2 mt-3">
                    <button class="btn btn-sx-danger">
                        <i class="bi bi-check2-circle me-1"></i> Salvar Checklist
                    </button>
                </div>

            </div>
        </div>

    </form>
</div>

<style>
:root{
  --sx-panel: rgba(18,18,20,.78);
  --sx-border: rgba(255,255,255,.08);
  --sx-text: rgba(255,255,255,.92);
  --sx-muted: rgba(255,255,255,.62);
}
.sx-glass{
  background: var(--sx-panel) !important;
  border: 1px solid var(--sx-border) !important;
  border-radius: 18px !important;
  box-shadow: 0 18px 45px rgba(0,0,0,.45) !important;
  backdrop-filter: blur(10px);
}
.sx-table-wrap{
  background: rgba(0,0,0,.25);
  border: 1px solid var(--sx-border);
  border-radius: 14px;
  overflow: hidden;
}
.table.sx-table{
  margin: 0;
  color: var(--sx-text) !important;
  background: transparent !important;
}
.table.sx-table thead th{
  background: rgba(255,255,255,.04) !important;
  border-bottom: 1px solid var(--sx-border) !important;
  color: rgba(255,255,255,.78) !important;
  font-size: 12px;
  letter-spacing: .4px;
  text-transform: uppercase;
}
.table.sx-table td, .table.sx-table th{
  border-color: rgba(255,255,255,.06) !important;
  background: transparent !important;
}

/* Inputs */
.sx-input, .sx-select, .sx-textarea{
  background: rgba(10,10,10,.55) !important;
  border: 1px solid rgba(255,255,255,.10) !important;
  color: #fff !important;
  border-radius: 12px !important;
  box-shadow: none !important;
}
.sx-input::placeholder, .sx-textarea::placeholder{ color: rgba(255,255,255,.35) !important; }
.sx-input:focus, .sx-select:focus, .sx-textarea:focus{
  border-color: rgba(255,42,42,.35) !important;
  box-shadow: 0 0 0 .22rem rgba(255,42,42,.14) !important;
}
.sx-file{
  background: rgba(10,10,10,.55) !important;
  border: 1px dashed rgba(255,255,255,.14) !important;
  color: rgba(255,255,255,.75) !important;
  border-radius: 12px !important;
}

/* Buttons */
.btn-sx-danger{
  background: linear-gradient(180deg, #ff3b3b, #ff1f1f) !important;
  border: 1px solid rgba(255,42,42,.35) !important;
  color: #fff !important;
  border-radius: 12px !important;
  box-shadow: 0 14px 30px rgba(255,42,42,.18) !important;
}
.btn-sx-outline{
  background: rgba(255,255,255,.04) !important;
  border: 1px solid rgba(255,255,255,.12) !important;
  color: rgba(255,255,255,.88) !important;
  border-radius: 12px !important;
}
.btn-sx-outline:hover{ background: rgba(255,255,255,.07) !important; }

/* Chips */
.sx-chip{
  display:inline-flex;
  align-items:center;
  gap:8px;
  padding: 7px 10px;
  border-radius: 12px;
  border: 1px solid rgba(255,255,255,.12);
  background: rgba(255,255,255,.03);
  color: rgba(255,255,255,.86);
  cursor:pointer;
  user-select:none;
}
.sx-chip input{ display:none; }
.sx-chip--ok.active{ border-color: rgba(0,255,140,.25); background: rgba(0,255,140,.10); }
.sx-chip--falha.active{ border-color: rgba(255,42,42,.28); background: rgba(255,42,42,.12); }
</style>

<script>
document.addEventListener('click', function(e){
  const label = e.target.closest('.sx-chip');
  if(!label) return;

  const container = label.parentElement;
  if(!container) return;

  container.querySelectorAll('.sx-chip').forEach(x => x.classList.remove('active'));
  label.classList.add('active');
});
</script>
@endsection
