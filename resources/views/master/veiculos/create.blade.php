@extends('layouts.app')

@section('page-heading')
    <div class="sx-page-header">
        <div>
            <h3 class="sx-page-title">Novo veículo</h3>
            <div class="sx-page-subtitle">Cadastre um veículo disponível para programação de viagens</div>
        </div>
        <div class="sx-page-actions">
            <a class="btn btn-outline-light btn-sm" href="{{ route('master.veiculos.index') }}">
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
        <form method="POST" action="{{ route('master.veiculos.store') }}" class="row g-3">
            @csrf
            <div class="col-md-3">
                <label class="form-label">Placa *</label>
                <input class="form-control" name="placa" value="{{ old('placa') }}" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Modelo *</label>
                <input class="form-control" name="modelo" value="{{ old('modelo') }}" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">Capacidade *</label>
                <input class="form-control" type="number" name="capacidade_passageiros" min="1" value="{{ old('capacidade_passageiros', 15) }}" required>
            </div>
            <div class="col-md-2">
                <label class="form-label">Status *</label>
                <select class="form-select" name="status_operacional">
                    <option value="liberado" @selected(old('status_operacional', 'liberado') === 'liberado')>Liberado</option>
                    <option value="bloqueado" @selected(old('status_operacional') === 'bloqueado')>Bloqueado</option>
                </select>
            </div>
            <div class="col-12 d-flex justify-content-end gap-2">
                <a class="btn btn-outline-light" href="{{ route('master.veiculos.index') }}">Cancelar</a>
                <button class="btn btn-systex"><i class="bi bi-check-circle"></i> Salvar veículo</button>
            </div>
        </form>
    </div>
</div>
@endsection
