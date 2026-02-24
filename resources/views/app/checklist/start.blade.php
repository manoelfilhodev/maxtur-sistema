<!doctype html>
<html lang="pt-br">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Checklist • SYSTEX</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <style>
    body{ background:#0b0b0b; color:#fff; min-height:100vh; }
    .sx-bg{ position:fixed; inset:0; background:
      radial-gradient(900px 500px at 15% 15%, rgba(255,42,42,.10), transparent 60%),
      radial-gradient(900px 500px at 85% 85%, rgba(255,42,42,.08), transparent 60%),
      linear-gradient(180deg,#0b0b0b,#101013);
      filter: saturate(1.1);
      z-index:-1;
    }
    .sx-card{
      background: rgba(18,18,20,.78);
      border: 1px solid rgba(255,255,255,.08);
      border-radius: 18px;
      box-shadow: 0 18px 45px rgba(0,0,0,.45);
      backdrop-filter: blur(10px);
    }
    .sx-input{
      background: rgba(10,10,10,.55) !important;
      border: 1px solid rgba(255,255,255,.10) !important;
      color:#fff !important;
      border-radius: 14px !important;
      padding: 14px 14px !important;
    }
    .sx-input::placeholder{ color: rgba(255,255,255,.35); }
    .sx-input:focus{ box-shadow: 0 0 0 .22rem rgba(255,42,42,.14) !important; border-color: rgba(255,42,42,.35) !important;}
    .btn-sx{
      background: linear-gradient(180deg,#ff3b3b,#ff1f1f);
      border: 1px solid rgba(255,42,42,.35);
      color:#fff;
      border-radius: 14px;
      padding: 14px 16px;
      font-weight: 800;
      box-shadow: 0 16px 34px rgba(255,42,42,.18);
    }
    .muted{ color: rgba(255,255,255,.62); }
    .brand{
      display:flex; align-items:center; gap:12px;
    }
    .mark{
      width:44px; height:44px; border-radius:14px; display:grid; place-items:center;
      background: rgba(255,42,42,.12); border:1px solid rgba(255,42,42,.22);
    }
  </style>
</head>
<body>
<div class="sx-bg"></div>

<div class="container py-4" style="max-width: 560px;">
  <div class="brand mb-3">
    <div class="mark">✓</div>
    <div>
      <div class="fw-black" style="font-weight:900; letter-spacing:.4px;">SYSTEX</div>
      <div class="muted" style="font-size:12px;">Checklist do Veículo • Modo Motorista</div>
    </div>
  </div>

  <div class="sx-card p-3 p-md-4">
    <h4 class="fw-bold mb-1">Iniciar Checklist</h4>
    <div class="muted mb-3">Preencha os dados e avance. Depois é 1 item por vez.</div>

    @if(session('error'))
      <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
    @if($errors->any())
      <div class="alert alert-danger">
        <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
      </div>
    @endif

    <form method="POST" action="{{ route('app.checklist.create') }}">
      @csrf

      <div class="mb-3">
        <label class="form-label muted">Placa *</label>
        <input class="form-control sx-input" name="placa" value="{{ old('placa') }}" placeholder="ABC1D23" required>
      </div>

      <div class="mb-3">
        <label class="form-label muted">Motorista *</label>
        <input class="form-control sx-input" name="motorista_nome" value="{{ old('motorista_nome') }}" placeholder="Nome do motorista" required>
      </div>

      <div class="mb-3">
        <label class="form-label muted">Modelo do veículo</label>
        <input class="form-control sx-input" name="modelo_veiculo" value="{{ old('modelo_veiculo') }}" placeholder="Van, micro-ônibus, ônibus...">
      </div>

      <div class="mb-3">
        <label class="form-label muted">Empresa fornecedora</label>
        <input class="form-control sx-input" name="empresa_fornecedora" value="{{ old('empresa_fornecedora') }}" placeholder="Maxtur / Terceiro...">
      </div>

      <div class="mb-4">
        <label class="form-label muted">Inspecionado por</label>
        <input class="form-control sx-input" name="inspecionado_por" value="{{ old('inspecionado_por') }}" placeholder="Quem está conferindo">
      </div>

      <button class="btn btn-sx w-100">Começar</button>
      <div class="muted text-center mt-3" style="font-size:12px;">Data: {{ now()->format('d/m/Y') }}</div>
    </form>
  </div>
</div>
</body>
</html>
