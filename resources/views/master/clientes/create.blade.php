@extends('layouts.app')

@section('page-heading')
<h3 class="text-white mb-0">Master - Novo Cliente</h3>
@endsection

@section('content')
<div class="dash-card p-3">
    <form method="POST" action="{{ route('master.clientes.store') }}" class="row g-3">
        @csrf
        <div class="col-md-6">
            <label class="form-label">Razao Social *</label>
            <input class="form-control" name="razao_social" value="{{ old('razao_social') }}" required>
        </div>
        <div class="col-md-6">
            <label class="form-label">Nome Fantasia</label>
            <input class="form-control" name="nome_fantasia" value="{{ old('nome_fantasia') }}">
        </div>
        <div class="col-md-4">
            <label class="form-label">CNPJ</label>
            <input class="form-control" name="cnpj" value="{{ old('cnpj') }}">
        </div>
        <div class="col-md-4">
            <label class="form-label">Email Cliente</label>
            <input class="form-control" name="email" type="email" value="{{ old('email') }}">
        </div>
        <div class="col-md-4">
            <label class="form-label">Telefone</label>
            <input class="form-control" name="telefone" value="{{ old('telefone') }}">
        </div>
        <div class="col-md-6">
            <label class="form-label">Cidade</label>
            <input class="form-control" name="cidade" value="{{ old('cidade') }}">
        </div>
        <div class="col-md-2">
            <label class="form-label">UF</label>
            <input class="form-control" name="uf" value="{{ old('uf') }}" maxlength="2">
        </div>
        <div class="col-md-4">
            <label class="form-label">Email CLIENT_ADMIN *</label>
            <input class="form-control" name="email_admin" type="email" value="{{ old('email_admin') }}" required>
        </div>
        <div class="col-md-6">
            <label class="form-label">Nome CLIENT_ADMIN</label>
            <input class="form-control" name="nome_admin" value="{{ old('nome_admin') }}">
        </div>
        <div class="col-12">
            <button class="btn btn-systex">Salvar e Criar CLIENT_ADMIN</button>
        </div>
    </form>
</div>
@endsection

