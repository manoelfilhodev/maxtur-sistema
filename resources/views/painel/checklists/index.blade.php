@extends('layouts.app')

@section('content')
<div class="container-fluid">

    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
        <div>
            <h3 class="fw-bold text-white mb-1">Checklists de Veículo</h3>
            <div class="text-muted" style="color: rgba(255,255,255,.65) !important;">
                Inspeções registradas (aprovado/reprovado)
            </div>
        </div>

        <a href="{{ route('checklists.create') }}" class="btn btn-sx-danger btn-sm">
            <i class="bi bi-plus-circle me-1"></i> Novo Checklist
        </a>
    </div>

    <div class="card sx-glass">
        <div class="card-body sx-card-body">

            @if($checklists->count())

                <div class="sx-table-wrap">
                    <table class="table sx-table align-middle m-0">
                        <thead>
                            <tr>
                                <th style="width:80px;">ID</th>
                                <th style="width:140px;">Data</th>
                                <th>Veículo</th>
                                <th>Motorista</th>
                                <th style="width:140px;">Status</th>
                                <th class="text-end" style="width:120px;">Ação</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach($checklists as $c)
                            <tr>
                                <td class="fw-bold">#{{ $c->id }}</td>
                                <td>{{ $c->data?->format('d/m/Y') }}</td>
                                <td>{{ $c->placa ?? ($c->veiculo_identificacao ?? '-') }}</td>
                                <td>{{ $c->motorista_nome ?? '-' }}</td>
                                <td>
                                    <span class="sx-badge {{ $c->status==='aprovado' ? 'ok' : ($c->status==='reprovado' ? 'bad' : '') }}">
                                        {{ strtoupper($c->status) }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('checklists.show', $c->id) }}" class="btn btn-sx-outline btn-sm">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    {{ $checklists->links() }}
                </div>

            @else
                <div class="text-center py-5 sx-muted">
                    Nenhum checklist registrado.
                </div>
            @endif

        </div>
    </div>

</div>

<style>
/* ===========================
   CARD GLASS
   =========================== */
.sx-glass{
    background: rgba(18,18,20,.78) !important;
    border: 1px solid rgba(255,255,255,.08) !important;
    border-radius: 18px !important;
    box-shadow: 0 18px 45px rgba(0,0,0,.45) !important;
}
.sx-card-body{ background: transparent !important; }

/* ===========================
   WRAPPER TABELA
   =========================== */
.sx-table-wrap{
    background: rgba(0,0,0,.35) !important;
    border: 1px solid rgba(255,255,255,.08) !important;
    border-radius: 14px !important;
    overflow: hidden !important;
}

/* ===========================
   🔥 BOOTSTRAP 5.3 FIX (VARS)
   =========================== */
.sx-table{
    --bs-table-bg: transparent !important;
    --bs-table-accent-bg: transparent !important;
    --bs-table-striped-bg: transparent !important;
    --bs-table-active-bg: rgba(255,255,255,.06) !important;
    --bs-table-hover-bg: rgba(255,255,255,.04) !important;

    --bs-table-color: rgba(255,255,255,.92) !important;
    --bs-table-border-color: rgba(255,255,255,.08) !important;

    color: rgba(255,255,255,.92) !important;
}
.sx-table > :not(caption) > * > *{
    background-color: transparent !important;
}

/* Cabeçalho */
.sx-table thead th{
    background: rgba(255,255,255,.05) !important;
    border-bottom: 1px solid rgba(255,255,255,.08) !important;
    color: rgba(255,255,255,.75) !important;
    font-size: 12px !important;
    letter-spacing: .4px !important;
    text-transform: uppercase !important;
}

/* Bordas e texto */
.sx-table td, .sx-table th{
    border-color: rgba(255,255,255,.08) !important;
}
.sx-table tbody tr:hover{
    background: rgba(255,255,255,.04) !important;
}
.sx-table tbody tr:hover > *{
    background-color: transparent !important;
}

.sx-muted{ color: rgba(255,255,255,.55) !important; }

/* Badge status */
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

/* Botões */
.btn-sx-danger{
    background: linear-gradient(180deg,#ff3b3b,#ff1f1f) !important;
    border: 1px solid rgba(255,42,42,.35) !important;
    color:#fff !important;
    border-radius: 12px !important;
    box-shadow: 0 16px 34px rgba(255,42,42,.14);
}
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
