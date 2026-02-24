@extends('layouts.app')

@section('page-heading')
<div class="d-flex align-items-start justify-content-between flex-wrap gap-2">
  <div>
    <h3 class="fw-bold mb-1 text-white">Novo Usuário</h3>
    <div class="text-muted" style="color: rgba(255,255,255,.65) !important;">
      Cadastre dados, permissões e jornada
    </div>
  </div>

  <div class="d-flex gap-2">
    <a href="{{ route('usuarios.index') }}" class="btn btn-outline-light btn-sm">
      <i class="bi bi-arrow-left me-1"></i> Voltar
    </a>
  </div>
</div>
@endsection

@section('content')
<style>
  .dash-card {
    background: rgba(18, 18, 20, .72) !important;
    border: 1px solid rgba(255, 255, 255, .08) !important;
    border-radius: 16px !important;
    box-shadow: 0 16px 45px rgba(0, 0, 0, .35);
    color: #fff;
  }
  .form-label {
    color: rgba(255,255,255,.75) !important;
    font-weight: 800 !important;
    letter-spacing: .2px;
  }
  .form-control, .form-select {
    background: rgba(255,255,255,.06) !important;
    border: 1px solid rgba(255,255,255,.12) !important;
    color: rgba(255,255,255,.90) !important;
    border-radius: 14px !important;
    padding: .65rem .85rem;
  }
  .form-control::placeholder { color: rgba(255,255,255,.55) !important; }
  .form-control:focus, .form-select:focus {
    border-color: rgba(255,42,42,.45) !important;
    box-shadow: 0 0 0 .2rem rgba(255,42,42,.10) !important;
  }
  .help-muted {
    color: rgba(255,255,255,.55) !important;
    font-size: 12px;
    margin-top: 6px;
    display: block;
  }
  .btn-systex {
    background: linear-gradient(135deg, rgba(255,42,42,.95), rgba(255,42,42,.70));
    border: 1px solid rgba(255,42,42,.35);
    color: #fff !important;
    font-weight: 900;
    letter-spacing: .2px;
    border-radius: 14px;
    padding: .65rem 1rem;
  }
  .btn-systex:hover { filter: brightness(1.05); transform: translateY(-1px); }
  .btn-ghost {
    border: 1px solid rgba(255,255,255,.16) !important;
    color: rgba(255,255,255,.86) !important;
    background: rgba(255,255,255,.04) !important;
    border-radius: 14px;
    padding: .65rem 1rem;
  }
  .btn-ghost:hover { background: rgba(255,255,255,.08) !important; }
  .divider { height: 1px; background: rgba(255,255,255,.08); margin: 16px 0; }
  .section-title { font-weight: 900; color: rgba(255,255,255,.92); margin: 0; }
  .section-sub { margin: 0; color: rgba(255,255,255,.60); font-size: 13px; }
  
  /* Corrige dropdown do select no dark */
.form-select option {
  background-color: #111 !important;
  color: #fff !important;
}

/* Para browsers que usam optgroup */
.form-select optgroup {
  background-color: #111 !important;
  color: #fff !important;
}

/* Hover / selecionado */
.form-select option:checked,
.form-select option:hover {
  background-color: rgba(255,42,42,.35) !important;
  color: #fff !important;
}

</style>

<div class="container-fluid px-4">
  <div class="dash-card p-3 p-md-4">

    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
      <div>
        <p class="section-title mb-1">Dados do usuário</p>
        <p class="section-sub">Preencha as informações principais, permissões e jornada.</p>
      </div>
    </div>

    <div class="divider"></div>

    @if($errors->any())
      <div class="alert alert-danger">
        <ul class="mb-0">
          @foreach($errors->all() as $e) <li>{{ $e }}</li> @endforeach
        </ul>
      </div>
    @endif

    <form action="{{ route('usuarios.store') }}" method="POST">
      @csrf

      <div class="row g-3">

        <div class="col-md-6">
          <label class="form-label">Nome</label>
          <input type="text" name="name" value="{{ old('name') }}" class="form-control" placeholder="Nome completo" required>
        </div>

        <div class="col-md-6">
          <label class="form-label">E-mail</label>
          <input type="email" name="email" value="{{ old('email') }}" class="form-control" placeholder="email@empresa.com" required>
        </div>

        <div class="col-md-4">
          <label class="form-label">CPF</label>
          <input type="text" name="cpf" value="{{ old('cpf') }}" class="form-control" placeholder="000.000.000-00" required>
          <small class="help-muted">Dica: mantenha o padrão do CPF igual ao cadastro.</small>
        </div>

        <div class="col-md-4">
          <label class="form-label">Nível</label>
          <select name="cargo" class="form-select">
            <option value="USUARIO" {{ old('cargo')=='USUARIO' ? 'selected' : '' }}>Usuário</option>
            <option value="GESTOR"  {{ old('cargo')=='GESTOR' ? 'selected' : '' }}>Gestor</option>
            <option value="ADMIN"   {{ old('cargo')=='ADMIN' ? 'selected' : '' }}>Administrador</option>
          </select>
          <small class="help-muted">Controle de permissões do painel.</small>
        </div>

        <div class="col-md-4">
          <label class="form-label">Senha</label>
          <input type="password" name="password" class="form-control" placeholder="Senha" required>
          <small class="help-muted">Mínimo recomendado: 6 caracteres.</small>
        </div>

        <div class="col-md-4">
          <label class="form-label">Ativo</label>
          <select name="ativo" class="form-select" required>
            <option value="1" {{ old('ativo', '1')=='1' ? 'selected' : '' }}>Sim</option>
            <option value="0" {{ old('ativo')=='0' ? 'selected' : '' }}>Não</option>
          </select>
          <small class="help-muted">Usuário inativo não deve bater ponto.</small>
        </div>

        <div class="col-md-4">
  <label class="form-label">Cliente</label>

  @if(!empty($clienteFixo))
    <input type="hidden" name="cliente_id" value="{{ $clienteFixo }}">
    <input type="text" class="form-control" value="Cliente #{{ $clienteFixo }}" disabled>
    <small class="help-muted">Vinculado automaticamente ao seu cliente.</small>
  @else
    <select name="cliente_id" class="form-select" required>
      <option value="">— Selecione —</option>
      @foreach(($clientes ?? []) as $c)
        <option value="{{ $c->id }}" {{ (string)old('cliente_id') === (string)$c->id ? 'selected' : '' }}>
          #{{ $c->id }} — {{ $c->nome }}
        </option>
      @endforeach
    </select>
    <small class="help-muted">Selecione qual cliente este usuário pertence.</small>
  @endif
</div>


        <div class="col-md-4">
          <label class="form-label">Jornada / Escala</label>
          <select name="jornada_id" id="jornada_id" class="form-select">
            <option value="">— Selecione —</option>
            @foreach(($jornadas ?? []) as $j)
              <option value="{{ $j['id'] }}" {{ old('jornada_id') == $j['id'] ? 'selected' : '' }}>
                {{ $j['nome'] ?? $j['id'] }}
              </option>
            @endforeach
          </select>
          <small class="help-muted">Se escolher <b>Diarista</b>, não exige padrão fixo de dias/horários.</small>
        </div>

        <div class="col-md-4" id="turno_wrap" style="display:none;">
          <label class="form-label">Turno</label>
          <select name="turno_id" id="turno_id" class="form-select">
            <option value="">— Selecione —</option>
          </select>
          <small class="help-muted">Aparece apenas para jornadas com turnos.</small>
        </div>

      </div>

      <div class="divider"></div>

      <div class="d-flex gap-2 flex-wrap">
        <button class="btn btn-systex">
          <i class="bi bi-check-circle me-1"></i> Salvar
        </button>

        <a href="{{ route('usuarios.index') }}" class="btn btn-ghost">
          Cancelar
        </a>
      </div>
    </form>

  </div>
</div>

<script>
  const jornadas = @json($jornadas ?? []);
  const selJornada = document.getElementById('jornada_id');
  const wrapTurno = document.getElementById('turno_wrap');
  const selTurno = document.getElementById('turno_id');

  const turnoAtual = @json(old('turno_id', ''));

  function renderTurnos(jornadaId){
    const j = jornadas.find(x => x.id === jornadaId);
    const turnos = (j && j.tipo === 'FIXA_TURNOS' && Array.isArray(j.turnos)) ? j.turnos : [];

    if(turnos.length === 0 || jornadaId === 'DIARISTA' || !jornadaId){
      wrapTurno.style.display = 'none';
      selTurno.innerHTML = '<option value="">— Selecione —</option>';
      selTurno.value = '';
      return;
    }

    wrapTurno.style.display = '';
    selTurno.innerHTML = '<option value="">— Selecione —</option>';

    turnos.forEach(t => {
      const opt = document.createElement('option');
      opt.value = t.id;
      opt.textContent = (t.nome ? t.nome : t.id) + ` (${t.entrada} → ${t.saida})`;
      selTurno.appendChild(opt);
    });

    if(turnoAtual){
      selTurno.value = turnoAtual;
    }
  }

  selJornada.addEventListener('change', () => renderTurnos(selJornada.value));
  renderTurnos(selJornada.value);
</script>
@endsection
