@extends('layouts.app')

@section('content')
<div class="container-fluid">

    {{-- Header --}}
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
        <div>
            <h3 class="fw-bold text-white mb-1">Checklist #{{ $checklist->id }}</h3>
            <div class="text-muted" style="color: rgba(255,255,255,.65) !important;">
                {{ $checklist->data?->format('d/m/Y') }}
                • Veículo: {{ $checklist->placa ?? '-' }}
                • Motorista: {{ $checklist->motorista_nome ?? '-' }}
            </div>
        </div>

        <a href="{{ route('checklists.index') }}" class="btn btn-sx-outline btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Voltar
        </a>
    </div>

    {{-- Status --}}
    <div class="mb-3">
        <span class="sx-badge {{ $checklist->status === 'aprovado' ? 'ok' : 'bad' }}">
            {{ strtoupper($checklist->status) }}
        </span>
    </div>

    {{-- Card --}}
    <div class="card sx-glass">
        <div class="card-body sx-card-body">

            <div class="sx-table-wrap">
                <table class="table sx-table align-middle m-0">
                    <thead>
                        <tr>
                            <th style="width:70px;">#</th>
                            <th>Item</th>
                            <th style="width:130px;">Status</th>
                            <th style="min-width:320px;">Como verificar</th>
                            <th style="min-width:220px;">Observação</th>
                            <th style="width:160px;">Foto</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($respostas as $r)
                        <tr>
                            <td class="fw-bold">#{{ $r->item->codigo }}</td>

                            <td>
                                <div class="fw-semibold text-white">{{ $r->item->titulo }}</div>
                            </td>

                            <td>
                                <span class="sx-badge {{ $r->status === 'ok' ? 'ok' : 'bad' }}">
                                    {{ strtoupper($r->status) }}
                                </span>
                            </td>

                            <td class="sx-muted">
                                {{ $r->item->como_verificar ?? '-' }}
                            </td>

                            <td class="sx-muted">
                                {{ $r->observacao ?: '—' }}
                            </td>

                            <td>
                                @if($r->foto_path)
                                    <a href="{{ asset('storage/'.$r->foto_path) }}" target="_blank" class="sx-thumb" title="Abrir foto">
                                        <img src="{{ asset('storage/'.$r->foto_path) }}" alt="Foto">
                                    </a>
                                @else
                                    <span class="sx-dash">—</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

        </div>
    </div>

</div>

<style>
/* ===========================
   CARD GLASS (SYSTEX)
   =========================== */
.sx-glass{
    background: rgba(18,18,20,.78) !important;
    border: 1px solid rgba(255,255,255,.08) !important;
    border-radius: 18px !important;
    box-shadow: 0 18px 45px rgba(0,0,0,.45) !important;
}
.sx-card-body{
    background: transparent !important;
}

/* ===========================
   WRAPPER DA TABELA
   =========================== */
.sx-table-wrap{
    background: rgba(0,0,0,.35) !important;
    border: 1px solid rgba(255,255,255,.08) !important;
    border-radius: 14px !important;
    overflow: hidden !important;
}

/* ===========================
   🔥 CORREÇÃO DEFINITIVA:
   Bootstrap 5.3 usa CSS VARS
   =========================== */
.sx-table{
    /* mata o branco da .table (bs vars) */
    --bs-table-bg: transparent !important;
    --bs-table-accent-bg: transparent !important;
    --bs-table-striped-bg: transparent !important;
    --bs-table-active-bg: rgba(255,255,255,.06) !important;
    --bs-table-hover-bg: rgba(255,255,255,.04) !important;

    --bs-table-color: rgba(255,255,255,.92) !important;
    --bs-table-border-color: rgba(255,255,255,.08) !important;

    color: rgba(255,255,255,.92) !important;
}

/* força transparência em todas as células */
.sx-table > :not(caption) > * > *{
    background-color: transparent !important;
}

/* cabeçalho */
.sx-table thead th{
    background: rgba(255,255,255,.05) !important;
    border-bottom: 1px solid rgba(255,255,255,.08) !important;
    color: rgba(255,255,255,.75) !important;
    font-size: 12px !important;
    letter-spacing: .4px !important;
    text-transform: uppercase !important;
}

/* bordas */
.sx-table td, .sx-table th{
    border-color: rgba(255,255,255,.08) !important;
}

/* hover */
.sx-table tbody tr:hover{
    background: rgba(255,255,255,.04) !important;
}
.sx-table tbody tr:hover > *{
    background-color: transparent !important;
}

/* textos */
.sx-muted{ color: rgba(255,255,255,.72) !important; }
.sx-dash{ color: rgba(255,255,255,.45) !important; }

/* badge status */
.sx-badge{
    padding: 6px 12px;
    border-radius: 999px;
    font-size: 12px;
    font-weight: 900;
    border: 1px solid rgba(255,255,255,.12);
    background: rgba(255,255,255,.05);
    display:inline-block;
}
.sx-badge.ok{
    border-color: rgba(0,255,140,.22);
    background: rgba(0,255,140,.14);
}
.sx-badge.bad{
    border-color: rgba(255,42,42,.28);
    background: rgba(255,42,42,.16);
}

/* thumb da foto */
.sx-thumb{
    display:inline-block;
    width: 60px;
    height: 60px;
    border-radius: 14px;
    overflow: hidden;
    border: 1px solid rgba(255,255,255,.14);
    background: rgba(255,255,255,.04);
}
.sx-thumb img{
    width:100%;
    height:100%;
    object-fit: cover;
}

/* botão */
.btn-sx-outline{
    background: rgba(255,255,255,.04) !important;
    border: 1px solid rgba(255,255,255,.12) !important;
    color: rgba(255,255,255,.9) !important;
    border-radius: 12px !important;
}
.btn-sx-outline:hover{
    background: rgba(255,255,255,.08) !important;
}
</style>
@endsection
