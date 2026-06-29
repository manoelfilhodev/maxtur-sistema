@extends('layouts.app')

@section('page-heading')
@include('partials.panel.page-header', [
    'title' => 'Nova viagem',
    'subtitle' => 'Informe programação, recursos e classificação operacional',
    'backRoute' => route('master.viagens.index'),
])
@endsection

@section('content')
<div class="sx-container">
@include('partials.panel.form-errors')
<div class="sx-card">
    <form method="POST" action="{{ route('master.viagens.store') }}" class="row g-3">
        @csrf
        <div class="col-md-4">
            <label class="form-label">Cliente *</label>
            <select class="form-select" name="cliente_id" required>
                <option value="">Selecione</option>
                @foreach($clientes as $cliente)
                    <option value="{{ $cliente->id }}" @selected((string) old('cliente_id') === (string) $cliente->id)>{{ $cliente->nome_fantasia ?: $cliente->razao_social }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label">Veiculo *</label>
            <select class="form-select" name="veiculo_id" required>
                <option value="">Selecione</option>
                @foreach($veiculos as $veiculo)
                    <option value="{{ $veiculo->id }}" @selected((string) old('veiculo_id') === (string) $veiculo->id)>{{ $veiculo->placa }} - {{ $veiculo->modelo }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label">Motorista *</label>
            <select class="form-select" name="motorista_id" required>
                <option value="">Selecione</option>
                @foreach($motoristas as $motorista)
                    <option value="{{ $motorista->id }}" @selected((string) old('motorista_id') === (string) $motorista->id)>{{ $motorista->nome }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label">Origem *</label>
            <input class="form-control" name="origem" value="{{ old('origem') }}" placeholder="Local de saída" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">Destino *</label>
            <input class="form-control" name="destino" value="{{ old('destino') }}" placeholder="Local de chegada" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">Status *</label>
            <select class="form-select" name="status" required>
                @foreach(['programada','em_andamento','realizada','cancelada','atrasada'] as $status)
                    <option value="{{ $status }}" @selected(old('status', 'programada') === $status)>{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3"><label class="form-label">Tipo/período *</label><select class="form-select" name="tipo_periodo" required><option value="diario" @selected(old('tipo_periodo')==='diario')>Diário</option><option value="mensal" @selected(old('tipo_periodo')==='mensal')>Mensal</option><option value="esporadico" @selected(old('tipo_periodo','esporadico')==='esporadico')>Esporádico</option></select></div>
        <div class="col-md-3"><label class="form-label">Natureza *</label><select class="form-select" name="natureza" required><option value="programada" @selected(old('natureza','programada')==='programada')>Programada</option><option value="extra" @selected(old('natureza')==='extra')>Extra</option></select></div>
        <div class="col-md-6">
            <label class="form-label">Data Prevista *</label>
            <input type="datetime-local" class="form-control" name="data_prevista" value="{{ old('data_prevista') }}" required>
        </div>
        <div class="col-md-6">
            <label class="form-label">Data Real</label>
            <input type="datetime-local" class="form-control" name="data_real" value="{{ old('data_real') }}">
        </div>
        <div class="col-12">
            <label class="form-label">Observações</label>
            <textarea class="form-control" name="observacoes" rows="3" placeholder="Informações adicionais para a operação">{{ old('observacoes') }}</textarea>
        </div>
        <div class="col-12 sx-form-actions">
            <a class="btn btn-outline-light" href="{{ route('master.viagens.index') }}">Cancelar</a>
            <button class="btn btn-systex"><i class="bi bi-check-circle"></i> Salvar viagem</button>
        </div>
    </form>
</div>
</div>
@endsection
