@extends('layouts.app')

@section('page-heading')
<div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
  <div>
    <h3 class="fw-bold mb-1">Detalhes do cliente</h3>
    <div class="text-muted">{{ $cliente->razao_social }}</div>
  </div>
  <div class="d-flex gap-2">
    <a href="{{ route('painel.clientes.edit', $cliente) }}" class="btn btn-outline-primary">Editar</a>
    <a href="{{ route('painel.clientes.index') }}" class="btn btn-outline-secondary">Voltar</a>
  </div>
</div>
@endsection

@section('content')
<div class="card shadow-sm border-0">
  <div class="card-body">
    <div class="row g-3">
      <div class="col-md-8">
        <div class="fw-semibold">Razão social</div>
        <div class="text-muted">{{ $cliente->razao_social }}</div>
      </div>
      <div class="col-md-4">
        <div class="fw-semibold">Status</div>
        <div>
          @if($cliente->ativo)
            <span class="badge bg-success-subtle text-success border border-success-subtle">Ativo</span>
          @else
            <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">Inativo</span>
          @endif
        </div>
      </div>

      <div class="col-md-4">
        <div class="fw-semibold">Fantasia</div>
        <div class="text-muted">{{ $cliente->nome_fantasia ?: '—' }}</div>
      </div>
      <div class="col-md-4">
        <div class="fw-semibold">Documento</div>
        <div class="text-muted">{{ $cliente->documento ?: '—' }}</div>
      </div>
      <div class="col-md-4">
        <div class="fw-semibold">E-mail</div>
        <div class="text-muted">{{ $cliente->email ?: '—' }}</div>
      </div>

      <div class="col-md-4">
        <div class="fw-semibold">Telefone</div>
        <div class="text-muted">{{ $cliente->telefone ?: '—' }}</div>
      </div>
      <div class="col-md-4">
        <div class="fw-semibold">WhatsApp</div>
        <div class="text-muted">{{ $cliente->whatsapp ?: '—' }}</div>
      </div>
      <div class="col-md-4">
        <div class="fw-semibold">Cidade/UF</div>
        <div class="text-muted">{{ $cliente->cidade ?: '—' }}{{ $cliente->uf ? '/'.$cliente->uf : '' }}</div>
      </div>

      <div class="col-12">
        <div class="fw-semibold">Observações</div>
        <div class="text-muted" style="white-space: pre-wrap;">{{ $cliente->observacoes ?: '—' }}</div>
      </div>
    </div>
  </div>
</div>
@endsection
