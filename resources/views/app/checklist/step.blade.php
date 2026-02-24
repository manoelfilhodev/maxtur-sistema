<!doctype html>
<html lang="pt-br">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Checklist • Item {{ $item->codigo }}</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <style>
    body{ background:#0b0b0b; color:#fff; min-height:100vh; }
    .sx-bg{ position:fixed; inset:0; background:
      radial-gradient(900px 500px at 15% 15%, rgba(255,42,42,.10), transparent 60%),
      radial-gradient(900px 500px at 85% 85%, rgba(255,42,42,.08), transparent 60%),
      linear-gradient(180deg,#0b0b0b,#101013);
      z-index:-1;
    }
    .sx-card{
      background: rgba(18,18,20,.78);
      border: 1px solid rgba(255,255,255,.08);
      border-radius: 18px;
      box-shadow: 0 18px 45px rgba(0,0,0,.45);
      backdrop-filter: blur(10px);
    }
    .muted{ color: rgba(255,255,255,.62); }
    .pill{
      display:inline-flex; gap:8px; align-items:center;
      padding: 6px 10px; border-radius:999px;
      background: rgba(255,255,255,.06);
      border: 1px solid rgba(255,255,255,.10);
      color: rgba(255,255,255,.8);
      font-size:12px; font-weight:700;
    }
    .big-btn{
      border-radius: 18px;
      padding: 16px;
      font-weight: 900;
      font-size: 18px;
      border: 1px solid rgba(255,255,255,.10);
      background: rgba(255,255,255,.06);
      color:#fff;
    }
    .big-btn.ok.active{ border-color: rgba(0,255,140,.25); background: rgba(0,255,140,.14); }
    .big-btn.falha.active{ border-color: rgba(255,42,42,.32); background: rgba(255,42,42,.16); }
    .sx-input{
      background: rgba(10,10,10,.55) !important;
      border: 1px solid rgba(255,255,255,.10) !important;
      color:#fff !important;
      border-radius: 14px !important;
      padding: 12px 14px !important;
    }
    .sx-file{
      background: rgba(10,10,10,.55) !important;
      border: 1px dashed rgba(255,255,255,.14) !important;
      color: rgba(255,255,255,.75) !important;
      border-radius: 14px !important;
      padding: 12px 14px !important;
    }
    .btn-next{
      background: linear-gradient(180deg,#ff3b3b,#ff1f1f);
      border: 1px solid rgba(255,42,42,.35);
      color:#fff;
      border-radius: 16px;
      padding: 14px 16px;
      font-weight: 900;
      box-shadow: 0 16px 34px rgba(255,42,42,.18);
    }
    .progress{
      height: 10px;
      background: rgba(255,255,255,.08);
      border-radius: 999px;
    }
    .progress-bar{
      background: linear-gradient(90deg,#ff2a2a,#ff5a5a);
    }
    .hint{
      border-left: 3px solid rgba(255,42,42,.55);
      padding-left: 12px;
      color: rgba(255,255,255,.82);
    }
  </style>
</head>
<body>
<div class="sx-bg"></div>

<div class="container py-3" style="max-width: 680px;">

  <div class="d-flex align-items-center justify-content-between mb-2">
    <div class="pill">Item {{ $item->codigo }} de {{ $total }}</div>
    <div class="pill">{{ $checklist->placa }} • {{ $checklist->motorista_nome }}</div>
  </div>

  <div class="progress mb-3">
    @php $pct = (int) round(($pos / max($total,1)) * 100); @endphp
    <div class="progress-bar" style="width: {{ $pct }}%"></div>
  </div>

  @if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
  @endif
  @if($errors->any())
    <div class="alert alert-danger">
      <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
  @endif

  <div class="sx-card p-3 p-md-4">
    <h4 class="fw-bold mb-1">{{ $item->titulo }}</h4>

    @if($item->como_verificar)
      <div class="hint mt-2 mb-3">
        <div class="muted" style="font-size:12px;">Como verificar</div>
        <div class="fw-semibold">{{ $item->como_verificar }}</div>
      </div>
    @endif

    <form method="POST" action="{{ route('app.checklist.step.save', [$checklist->id, $item->codigo]) }}" enctype="multipart/form-data">
      @csrf

      <input type="hidden" id="status" name="status" value="{{ old('status', $resp->status ?? '') }}">

      <div class="row g-2 mb-3">
        <div class="col-6">
          <button type="button" id="btnOk" class="w-100 big-btn ok">✅ OK</button>
        </div>
        <div class="col-6">
          <button type="button" id="btnFalha" class="w-100 big-btn falha">⚠️ FALHA</button>
        </div>
      </div>

      <div class="mb-3">
        <label class="form-label muted">Observação (opcional)</label>
        <input class="form-control sx-input" name="observacao" value="{{ old('observacao', $resp->observacao ?? '') }}" placeholder="Ex.: cinto travando, porta com ruído...">
      </div>

      <div class="mb-3">
        <label class="form-label muted">Foto (obrigatória se FALHA)</label>
        <input class="form-control sx-file" type="file" name="foto" accept="image/*" capture="environment" id="foto">
        <div class="muted mt-2" id="fotoHint" style="font-size:12px; display:none;">
          Foto obrigatória para FALHA.
        </div>
      </div>

      <button class="btn btn-next w-100">Salvar e continuar</button>
    </form>
  </div>
</div>

<script>
(function(){
  const status = document.getElementById('status');
  const btnOk = document.getElementById('btnOk');
  const btnFalha = document.getElementById('btnFalha');
  const fotoHint = document.getElementById('fotoHint');

  function setActive(val){
    status.value = val;
    btnOk.classList.toggle('active', val === 'ok');
    btnFalha.classList.toggle('active', val === 'falha');
    fotoHint.style.display = (val === 'falha') ? 'block' : 'none';
  }

  btnOk.addEventListener('click', () => setActive('ok'));
  btnFalha.addEventListener('click', () => setActive('falha'));

  // estado inicial
  if(status.value) setActive(status.value);
})();
</script>
</body>
</html>
