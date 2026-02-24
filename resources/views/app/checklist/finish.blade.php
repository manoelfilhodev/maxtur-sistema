<!doctype html>
<html lang="pt-br">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Checklist Enviado</title>
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
    .badge-ok{ background: rgba(0,255,140,.12); border:1px solid rgba(0,255,140,.22); padding:8px 12px; border-radius:999px; font-weight:900;}
    .badge-bad{ background: rgba(255,42,42,.14); border:1px solid rgba(255,42,42,.28); padding:8px 12px; border-radius:999px; font-weight:900;}
    .btn-sx{
      background: rgba(255,255,255,.06);
      border: 1px solid rgba(255,255,255,.12);
      color:#fff;
      border-radius: 14px;
      padding: 12px 14px;
      font-weight: 800;
    }
  </style>
</head>
<body>
<div class="sx-bg"></div>

<div class="container py-5" style="max-width: 560px;">
  <div class="sx-card p-4 text-center">
    <div style="font-size:42px;">✅</div>
    <h4 class="fw-bold mb-1">Checklist enviado</h4>
    <div class="muted mb-3">Obrigado! Seu checklist foi registrado no sistema.</div>

    <div class="d-flex justify-content-center mb-3">
      @if($checklist->status === 'aprovado')
        <div class="badge-ok">APROVADO</div>
      @else
        <div class="badge-bad">REPROVADO</div>
      @endif
    </div>

    <div class="muted mb-4">
      Placa: <span class="text-white fw-semibold">{{ $checklist->placa }}</span><br>
      Motorista: <span class="text-white fw-semibold">{{ $checklist->motorista_nome }}</span><br>
      Data: <span class="text-white fw-semibold">{{ $checklist->data?->format('d/m/Y') }}</span>
    </div>

    <a href="{{ route('app.checklist.start') }}" class="btn btn-sx w-100">Fazer novo checklist</a>
    <div class="muted mt-3" style="font-size:12px;">SYSTEX • Controle de Frota</div>
  </div>
</div>
</body>
</html>
