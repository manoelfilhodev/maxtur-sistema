@extends('layouts.app')

@section('page-heading')
<div class="d-flex align-items-start justify-content-between flex-wrap gap-2">
    <div>
        <h3 class="fw-bold mb-1 text-white">Detalhe do feedback #{{ $feedback->id }}</h3>
        <div class="text-muted" style="color: rgba(255,255,255,.65) !important;">
            Visualização completa do retorno enviado pelo funcionário.
        </div>
    </div>
    @if(auth()->user()->isMaster())
        <a href="{{ route('painel.feedbacks.index') }}" class="btn btn-outline-light btn-sm">Voltar</a>
    @else
        <a href="{{ route('tenant.feedbacks.index') }}" class="btn btn-outline-light btn-sm">Voltar</a>
    @endif
</div>
@endsection

@section('content')
<div class="dash-card p-4">
    <div class="row g-3 mb-3">
        <div class="col-md-3">
            <div class="small text-muted">Tipo</div>
            <div class="mt-1">
                @if($feedback->tipo === 'critica')
                    <span class="badge bg-danger">Crítica</span>
                @else
                    <span class="badge bg-info">Sugestão</span>
                @endif
            </div>
        </div>
        <div class="col-md-3">
            <div class="small text-muted">Status</div>
            <div class="fw-semibold text-white mt-1">{{ ucfirst($feedback->status) }}</div>
        </div>
        <div class="col-md-3">
            <div class="small text-muted">Funcionário</div>
            <div class="fw-semibold text-white mt-1">{{ $feedback->funcionario->name ?? '-' }}</div>
        </div>
        <div class="col-md-3">
            <div class="small text-muted">Data/hora</div>
            <div class="fw-semibold text-white mt-1">{{ optional($feedback->created_at)->format('d/m/Y H:i') }}</div>
        </div>
    </div>

    <div class="small text-muted mb-2">Mensagem</div>
    <div class="p-3 rounded" style="background: rgba(255,255,255,.04); border: 1px solid rgba(255,255,255,.08); white-space: pre-wrap;">
        {{ $feedback->mensagem }}
    </div>
</div>
@endsection
