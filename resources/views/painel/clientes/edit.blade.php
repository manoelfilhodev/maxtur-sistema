@extends('layouts.app')

@section('page-heading')
    <div class="d-flex align-items-start justify-content-between flex-wrap gap-2">
        <div>
            <h3 class="fw-bold mb-1 text-white">Editar cliente</h3>
            <div class="text-muted" style="color: rgba(255,255,255,.65) !important;">
                {{ $cliente->razao_social }}
            </div>
        </div>

        <div class="d-flex gap-2">
            <a href="{{ route('painel.clientes.index') }}" class="btn btn-outline-light btn-sm">
                <i class="bi bi-arrow-left me-1"></i> Voltar
            </a>
        </div>
    </div>
@endsection

@section('content')
<style>
/* =========================
   CARD / CONTAINER (GLASS)
========================== */
.systex-clientes-form .dash-card{
    background: rgba(18, 18, 20, .72) !important;
    border: 1px solid rgba(255, 255, 255, .08) !important;
    border-radius: 16px !important;
    box-shadow: 0 16px 45px rgba(0, 0, 0, .35);
    color: #fff;
}

/* Textos */
.systex-clientes-form .muted{
    color: rgba(255,255,255,.64) !important;
}

/* Labels */
.systex-clientes-form .form-label{
    color: rgba(255,255,255,.75) !important;
    font-weight: 800;
    font-size: 12px;
    letter-spacing: .25px;
    text-transform: uppercase;
}

/* Inputs dark */
.systex-clientes-form .form-control,
.systex-clientes-form .form-select,
.systex-clientes-form textarea.form-control{
    background: rgba(255,255,255,.06) !important;
    border: 1px solid rgba(255,255,255,.10) !important;
    color: rgba(255,255,255,.90) !important;
    border-radius: 12px !important;
    padding: 10px 12px !important;
}

.systex-clientes-form .form-control::placeholder,
.systex-clientes-form textarea.form-control::placeholder{
    color: rgba(255,255,255,.45) !important;
}

.systex-clientes-form .form-control:focus,
.systex-clientes-form .form-select:focus,
.systex-clientes-form textarea.form-control:focus{
    box-shadow: 0 0 0 .2rem rgba(255,42,42,.12) !important;
    border-color: rgba(255,42,42,.35) !important;
}

/* Alerts */
.systex-clientes-form .alert{
    border: 1px solid rgba(255,255,255,.10) !important;
    border-radius: 14px;
}

/* Botões */
.systex-clientes-form .btn-outline-light{
    border-color: rgba(255,255,255,.18) !important;
    color: rgba(255,255,255,.86) !important;
    background: rgba(255,255,255,.04) !important;
    border-radius: 12px !important;
}
.systex-clientes-form .btn-outline-light:hover{
    background: rgba(255,255,255,.08) !important;
}

/* Divisor */
.systex-clientes-form .soft-divider{
    height: 1px;
    background: rgba(255,255,255,.08);
    margin: 14px 0;
}

/* Ajuda / hint */
.systex-clientes-form .hint{
    font-size: 12px;
    color: rgba(255,255,255,.55);
}
</style>

<div class="container-fluid px-4 systex-clientes-form">

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center" role="alert">
            <i class="bi bi-exclamation-triangle-fill fs-5 me-2"></i>
            <div>
                <div class="fw-bold">Ops! Verifique os campos.</div>
                <div class="small">
                    @foreach ($errors->all() as $error)
                        <div>• {{ $error }}</div>
                    @endforeach
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="dash-card p-3 p-lg-4">

        <div class="d-flex align-items-start justify-content-between flex-wrap gap-2">
            <div>
                <div class="fw-bold text-white" style="font-size: 14px;">Dados do cliente</div>
                <div class="hint">
                    Atualize os dados do cliente. As alterações entram em vigor imediatamente.
                </div>
            </div>

            <div class="muted small">
                Última atualização:
                <span class="text-white fw-bold">
                    {{ $cliente->updated_at?->format('d/m/Y H:i') ?? '—' }}
                </span>
            </div>
        </div>

        <div class="soft-divider"></div>

        <form method="POST" action="{{ route('painel.clientes.update', $cliente->id) }}">
            @csrf
            @method('PUT')

            @include('painel.clientes._form', ['cliente' => $cliente, 'isCreate' => false])

            <div class="d-flex justify-content-end gap-2 mt-4">
                <a href="{{ route('painel.clientes.index') }}" class="btn btn-outline-light">
                    Cancelar
                </a>
                <button class="btn btn-systex">
                    <i class="bi bi-check2-circle me-1"></i> Atualizar
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
