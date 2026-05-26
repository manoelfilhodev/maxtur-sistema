@extends('layouts.app')

@section('page-heading')
    <div class="sx-page-header">
        <div>
            <h3 class="sx-page-title">Novo motorista</h3>
            <div class="sx-page-subtitle">Crie um usuário motorista para execução e atribuição de viagens</div>
        </div>
        <div class="sx-page-actions">
            <a class="btn btn-outline-light btn-sm" href="{{ route('master.motoristas.index') }}">
                <i class="bi bi-arrow-left"></i> Voltar
            </a>
        </div>
    </div>
@endsection

@section('content')
<div class="sx-container">
    @if($errors->any())
        <div class="alert alert-danger">
            @foreach($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <div class="sx-card">
        <form method="POST" action="{{ route('master.motoristas.store') }}" class="row g-3">
            @csrf
            <div class="col-md-6">
                <label class="form-label">Nome *</label>
                <input class="form-control" name="name" value="{{ old('name') }}" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">E-mail de acesso *</label>
                <input class="form-control" type="email" name="email" value="{{ old('email') }}" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Documento/CNH</label>
                <input class="form-control" name="cpf" value="{{ old('cpf') }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">Telefone</label>
                <input class="form-control" name="telefone" value="{{ old('telefone') }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">Status</label>
                <select class="form-select" name="ativo">
                    <option value="1" @selected(old('ativo', '1') === '1')>Ativo</option>
                    <option value="0" @selected(old('ativo') === '0')>Inativo</option>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Senha inicial *</label>
                <input class="form-control" type="password" name="password" required>
            </div>
            <div class="col-12 d-flex justify-content-end gap-2">
                <a class="btn btn-outline-light" href="{{ route('master.motoristas.index') }}">Cancelar</a>
                <button class="btn btn-systex"><i class="bi bi-check-circle"></i> Salvar motorista</button>
            </div>
        </form>
    </div>
</div>
@endsection
