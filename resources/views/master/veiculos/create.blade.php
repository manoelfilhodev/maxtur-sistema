@extends('layouts.app')

@section('page-heading')
<h3 class="text-white mb-0">Master - Novo Veiculo</h3>
@endsection

@section('content')
<div class="dash-card p-3">
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
                <option value="liberado">liberado</option>
                <option value="bloqueado">bloqueado</option>
            </select>
        </div>
        <div class="col-12">
            <button class="btn btn-systex">Salvar</button>
        </div>
    </form>
</div>
@endsection

