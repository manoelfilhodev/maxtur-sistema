@extends('layouts.app')

@section('page-heading')
<h3 class="text-white mb-0">Master - Novo Motorista</h3>
@endsection

@section('content')
<div class="dash-card p-3">
    <form method="POST" action="{{ route('master.motoristas.store') }}" class="row g-3">
        @csrf
        <div class="col-md-5">
            <label class="form-label">Nome *</label>
            <input class="form-control" name="nome" value="{{ old('nome') }}" required>
        </div>
        <div class="col-md-3">
            <label class="form-label">CNH</label>
            <input class="form-control" name="cnh" value="{{ old('cnh') }}">
        </div>
        <div class="col-md-2">
            <label class="form-label">Telefone</label>
            <input class="form-control" name="telefone" value="{{ old('telefone') }}">
        </div>
        <div class="col-md-2">
            <label class="form-label">Status</label>
            <select class="form-select" name="ativo">
                <option value="1">Ativo</option>
                <option value="0">Inativo</option>
            </select>
        </div>
        <div class="col-12">
            <button class="btn btn-systex">Salvar</button>
        </div>
    </form>
</div>
@endsection

