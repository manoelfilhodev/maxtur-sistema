@extends('layouts.app')

@section('page-heading')
    <h3 class="text-white mb-0">Operador - Controle de Atrasos</h3>
@endsection

@section('content')
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card mb-3">
        <div class="card-body">
            <h5>Registrar atraso</h5>
            <form method="POST" action="" id="form-atraso-viagem" class="row g-2 mb-2">
                @csrf
                <div class="col-md-3">
                    <select id="solicitacao-viagem" class="form-select" required>
                        <option value="">Selecione solicitacao</option>
                        @foreach($solicitacoes as $solicitacao)
                            <option value="{{ $solicitacao->id }}">#{{ $solicitacao->id }} - {{ $solicitacao->origem }} > {{ $solicitacao->destino }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="number" min="1" name="minutos_atraso" class="form-control" placeholder="Minutos" required>
                </div>
                <div class="col-md-4">
                    <input type="text" name="motivo" class="form-control" placeholder="Motivo">
                </div>
                <div class="col-md-2">
                    <button class="btn btn-systex w-100">Atraso viagem</button>
                </div>
            </form>

            <form method="POST" action="" id="form-atraso-passageiro" class="row g-2">
                @csrf
                <div class="col-md-3">
                    <select id="solicitacao-passageiro" class="form-select" required>
                        <option value="">Selecione solicitacao</option>
                        @foreach($solicitacoes as $solicitacao)
                            <option value="{{ $solicitacao->id }}">#{{ $solicitacao->id }} - {{ $solicitacao->origem }} > {{ $solicitacao->destino }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <input type="number" min="1" name="passageiro_id" class="form-control" placeholder="Passageiro ID" required>
                </div>
                <div class="col-md-2">
                    <input type="number" min="1" name="minutos_atraso" class="form-control" placeholder="Minutos" required>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-systex w-100">Atraso passageiro</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <h5>Atrasos de viagem</h5>
            <div class="table-responsive">
                <table class="table table-dark table-striped">
                    <thead><tr><th>Solicitacao</th><th>Cliente</th><th>Min</th><th>Motivo</th><th>Data</th></tr></thead>
                    <tbody>
                        @forelse($atrasosViagem as $atraso)
                            <tr>
                                <td>#{{ $atraso->solicitacao_id }}</td>
                                <td>{{ $atraso->cliente->nome_fantasia ?? $atraso->cliente->razao_social ?? '-' }}</td>
                                <td>{{ $atraso->minutos_atraso }}</td>
                                <td>{{ $atraso->motivo ?? '-' }}</td>
                                <td>{{ $atraso->created_at->format('d/m/Y H:i') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5">Sem registros.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $atrasosViagem->links() }}
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <h5>Atrasos por passageiro</h5>
            <div class="table-responsive">
                <table class="table table-dark table-striped">
                    <thead><tr><th>Solicitacao</th><th>Passageiro</th><th>Min</th><th>Motivo</th><th>Data</th></tr></thead>
                    <tbody>
                        @forelse($atrasosPassageiro as $atraso)
                            <tr>
                                <td>#{{ $atraso->solicitacao_id }}</td>
                                <td>{{ $atraso->passageiro->nome ?? '-' }}</td>
                                <td>{{ $atraso->minutos_atraso }}</td>
                                <td>{{ $atraso->motivo ?? '-' }}</td>
                                <td>{{ $atraso->created_at->format('d/m/Y H:i') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5">Sem registros.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $atrasosPassageiro->links() }}
        </div>
    </div>

    <script>
        (function () {
            const viagemSelect = document.getElementById('solicitacao-viagem');
            const viagemForm = document.getElementById('form-atraso-viagem');
            const passageiroSelect = document.getElementById('solicitacao-passageiro');
            const passageiroForm = document.getElementById('form-atraso-passageiro');

            const buildUrl = (id, tipo) => `/painel/operador/solicitacoes/${id}/${tipo}`;

            viagemForm.addEventListener('submit', function () {
                viagemForm.action = buildUrl(viagemSelect.value, 'atraso');
            });

            passageiroForm.addEventListener('submit', function () {
                passageiroForm.action = buildUrl(passageiroSelect.value, 'atraso-passageiro');
            });
        })();
    </script>
@endsection

