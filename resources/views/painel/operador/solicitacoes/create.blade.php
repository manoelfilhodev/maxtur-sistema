@extends('layouts.app')

@section('page-heading')
    @include('partials.panel.page-header', [
        'title' => 'Nova viagem',
        'subtitle' => 'Cadastre a solicitação e os passageiros previstos',
        'backRoute' => route('painel.operador.solicitacoes.index'),
    ])
@endsection

@section('content')
    <div class="sx-container">
    @include('partials.panel.form-errors')

    <div class="sx-card">
        <div class="card-body">
            <form method="POST" action="{{ route('painel.operador.solicitacoes.store') }}" class="row g-3">
                @csrf

                <div class="col-md-6">
                    <label class="form-label">Cliente</label>
                    <select name="cliente_id" class="form-select" required>
                        <option value="">Selecione o cliente</option>
                        @foreach($clientes as $cliente)
                            <option value="{{ $cliente->id }}" @selected(old('cliente_id') == $cliente->id)>
                                {{ $cliente->nome_fantasia ?? $cliente->razao_social }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Data/Hora</label>
                    <input type="datetime-local" name="data_hora" value="{{ old('data_hora') }}" class="form-control" required>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Passageiros previstos</label>
                    <input type="number" min="0" name="passageiros_previstos" value="{{ old('passageiros_previstos', 0) }}" class="form-control">
                </div>

                <div class="col-md-3">
                    <label class="form-label">Tipo/período</label>
                    <select name="tipo_periodo" class="form-select" required>
                        <option value="diario" @selected(old('tipo_periodo') === 'diario')>Diário</option>
                        <option value="mensal" @selected(old('tipo_periodo') === 'mensal')>Mensal</option>
                        <option value="esporadico" @selected(old('tipo_periodo', 'esporadico') === 'esporadico')>Esporádico</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Natureza</label>
                    <select name="natureza" class="form-select" required>
                        <option value="programada" @selected(old('natureza', 'programada') === 'programada')>Programada</option>
                        <option value="extra" @selected(old('natureza') === 'extra')>Extra</option>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Origem</label>
                    <input type="text" name="origem" value="{{ old('origem') }}" class="form-control" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Destino</label>
                    <input type="text" name="destino" value="{{ old('destino') }}" class="form-control" required>
                </div>

                <div class="col-12">
                    <label class="form-label">Passageiros</label>
                    <select name="passageiro_ids[]" class="form-select" multiple size="8">
                        @foreach($passageiros as $passageiro)
                            <option value="{{ $passageiro->id }}" @selected(in_array($passageiro->id, old('passageiro_ids', [])))>
                                {{ $passageiro->nome }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12">
                    <label class="form-label">Observação</label>
                    <textarea name="observacao" class="form-control" rows="3">{{ old('observacao') }}</textarea>
                </div>

                <div class="col-12 sx-form-actions">
                    <a href="{{ route('painel.operador.solicitacoes.index') }}" class="btn btn-outline-light">Cancelar</a>
                    <button class="btn btn-systex"><i class="bi bi-check-circle"></i> Criar solicitação</button>
                </div>
            </form>
        </div>
    </div>
    </div>
@endsection
