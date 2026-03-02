@extends('layouts.app')

@section('page-heading')
<h3 class="text-white mb-0">Master - Nova Viagem</h3>
@endsection

@section('content')
<div class="dash-card p-3">
    <form method="POST" action="{{ route('master.viagens.store') }}" class="row g-3">
        @csrf
        <div class="col-md-4">
            <label class="form-label">Cliente *</label>
            <select class="form-select" name="cliente_id" required>
                <option value="">Selecione</option>
                @foreach($clientes as $cliente)
                    <option value="{{ $cliente->id }}">{{ $cliente->nome_fantasia ?: $cliente->razao_social }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label">Veiculo *</label>
            <select class="form-select" name="veiculo_id" required>
                <option value="">Selecione</option>
                @foreach($veiculos as $veiculo)
                    <option value="{{ $veiculo->id }}">{{ $veiculo->placa }} - {{ $veiculo->modelo }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label">Motorista *</label>
            <select class="form-select" name="motorista_id" required>
                <option value="">Selecione</option>
                @foreach($motoristas as $motorista)
                    <option value="{{ $motorista->id }}">{{ $motorista->nome }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label">Origem *</label>
            <input class="form-control" name="origem" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">Destino *</label>
            <input class="form-control" name="destino" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">Status *</label>
            <select class="form-select" name="status" required>
                @foreach(['programada','em_andamento','realizada','cancelada','atrasada'] as $status)
                    <option value="{{ $status }}">{{ $status }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label">Data Prevista *</label>
            <input type="datetime-local" class="form-control" name="data_prevista" required>
        </div>
        <div class="col-md-6">
            <label class="form-label">Data Real</label>
            <input type="datetime-local" class="form-control" name="data_real">
        </div>
        <div class="col-12">
            <label class="form-label">Observacoes</label>
            <textarea class="form-control" name="observacoes" rows="3"></textarea>
        </div>
        <div class="col-12">
            <button class="btn btn-systex">Salvar Viagem</button>
        </div>
    </form>
</div>
@endsection

