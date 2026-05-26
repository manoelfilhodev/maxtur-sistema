@extends('layouts.app')

@section('page-heading')
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
        <h3 class="text-white mb-0">Nova viagem</h3>
        <a href="{{ route('painel.operador.solicitacoes.index') }}" class="btn btn-outline-light btn-sm">Voltar</a>
    </div>
@endsection

@section('content')
    @if($errors->any())
        <div class="alert alert-danger">
            @foreach($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <div class="card">
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

                <div class="col-12 d-flex justify-content-end">
                    <button class="btn btn-systex">Criar solicitação</button>
                </div>
            </form>
        </div>
    </div>
@endsection
