@extends('layouts.app')

@section('page-heading')
    <h3 class="text-white mb-0">Solicitacao #{{ $solicitacao->id }}</h3>
@endsection

@section('content')
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card mb-3">
        <div class="card-body">
            <p><strong>Cliente:</strong> {{ $solicitacao->cliente->nome_fantasia ?? $solicitacao->cliente->razao_social ?? '-' }}</p>
            <p><strong>Origem:</strong> {{ $solicitacao->origem }}</p>
            <p><strong>Destino:</strong> {{ $solicitacao->destino }}</p>
            <p><strong>Status:</strong> {{ $solicitacao->status }}</p>

            <form method="POST" action="{{ route('painel.operador.solicitacoes.status', $solicitacao->id) }}" class="row g-2 mb-3">
                @csrf
                @method('PATCH')
                <div class="col-md-4">
                    <select name="status" class="form-select" required>
                        @foreach(['aberta','em_analise','aprovada','programada','realizada','cancelada','rejeitada'] as $status)
                            <option value="{{ $status }}" @selected($solicitacao->status === $status)>{{ $status }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-systex w-100">Atualizar status</button>
                </div>
            </form>

            <form method="POST" action="{{ route('painel.operador.solicitacoes.atribuir', $solicitacao->id) }}" class="row g-2">
                @csrf
                @method('PATCH')
                <div class="col-md-4">
                    <select name="veiculo_id" class="form-select" required>
                        <option value="">Selecione veiculo</option>
                        @foreach($veiculos as $veiculo)
                            <option value="{{ $veiculo->id }}">{{ $veiculo->placa }} - {{ $veiculo->modelo }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <select name="motorista_id" class="form-select" required>
                        <option value="">Selecione motorista</option>
                        @foreach($motoristas as $motorista)
                            <option value="{{ $motorista->id }}">{{ $motorista->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-systex w-100">Atribuir</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <h5>Passageiros</h5>
            <ul>
                @forelse($solicitacao->passageiros as $passageiro)
                    <li>{{ $passageiro->nome }}</li>
                @empty
                    <li>Sem passageiros vinculados.</li>
                @endforelse
            </ul>
        </div>
    </div>
@endsection

