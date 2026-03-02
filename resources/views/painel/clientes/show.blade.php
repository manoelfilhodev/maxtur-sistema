@extends('layouts.app')

@section('page-heading')
<div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
  <div>
    <h3 class="fw-bold mb-1 text-white">Detalhes do cliente</h3>
    <div class="text-muted" style="color: rgba(255,255,255,.65) !important;">{{ $cliente->razao_social }}</div>
  </div>
  <div class="d-flex gap-2">
    <a href="{{ route('painel.clientes.edit', $cliente->id) }}" class="btn btn-outline-light btn-sm">Editar</a>
    <a href="{{ route('painel.clientes.index') }}" class="btn btn-outline-light btn-sm">Voltar</a>
  </div>
</div>
@endsection

@section('content')
@if(session('ativacao_link'))
  <div class="alert alert-warning">
    <strong>Conta do cliente criada. Envie este link para ativacao:</strong><br>
    Email: {{ session('ativacao_link.email') }}<br>
    <a href="{{ session('ativacao_link.link') }}" target="_blank">{{ session('ativacao_link.link') }}</a>
  </div>
@endif

<div class="dash-card p-3 mb-3">
  <div class="row g-3">
    <div class="col-md-8">
      <div class="fw-semibold text-white">Razao social</div>
      <div class="text-muted">{{ $cliente->razao_social }}</div>
    </div>
    <div class="col-md-4">
      <div class="fw-semibold text-white">Status</div>
      <div class="text-muted">{{ $cliente->ativo ? 'Ativo' : 'Inativo' }}</div>
    </div>
    <div class="col-md-4"><div class="fw-semibold text-white">Fantasia</div><div class="text-muted">{{ $cliente->nome_fantasia ?: '-' }}</div></div>
    <div class="col-md-4"><div class="fw-semibold text-white">CNPJ</div><div class="text-muted">{{ $cliente->cnpj ?: '-' }}</div></div>
    <div class="col-md-4"><div class="fw-semibold text-white">Documento</div><div class="text-muted">{{ $cliente->documento ?: '-' }}</div></div>
    <div class="col-md-4"><div class="fw-semibold text-white">Email</div><div class="text-muted">{{ $cliente->email ?: '-' }}</div></div>
    <div class="col-md-4"><div class="fw-semibold text-white">Telefone</div><div class="text-muted">{{ $cliente->telefone ?: '-' }}</div></div>
    <div class="col-md-4"><div class="fw-semibold text-white">WhatsApp</div><div class="text-muted">{{ $cliente->whatsapp ?: '-' }}</div></div>
    <div class="col-md-4"><div class="fw-semibold text-white">Cidade/UF</div><div class="text-muted">{{ $cliente->cidade ?: '-' }}{{ $cliente->uf ? '/'.$cliente->uf : '' }}</div></div>
  </div>
</div>

@php
  $clientAdmin = $cliente->clientUsers->firstWhere('role', 'CLIENT_ADMIN');
@endphp
<div class="dash-card p-3">
  <div class="fw-semibold text-white mb-2">Acesso do cliente</div>
  <p class="mb-1"><strong>CLIENT_ADMIN:</strong> {{ $clientAdmin?->email ?? 'Nao encontrado' }}</p>
  <p class="mb-3"><strong>Ativada em:</strong> {{ $clientAdmin?->activated_at?->format('d/m/Y H:i') ?? 'Pendente' }}</p>

  @if($clientAdmin)
    <form method="POST" action="{{ route('painel.clientes.reenviar-ativacao', $cliente->id) }}">
      @csrf
      <button class="btn btn-systex btn-sm">Gerar novo link de ativacao</button>
    </form>
  @endif
</div>
@endsection

