@extends('layouts.app')

@section('page-heading')
    @include('partials.panel.page-header', [
        'title' => 'Atrasos e Ocorrências',
        'subtitle' => 'Registro e acompanhamento de desvios operacionais das viagens',
    ])
@endsection

@section('content')
<div class="sx-container">
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="sx-card mb-3">
        <div class="sx-card-header">
            <div>
                <h5 class="sx-card-title">Registrar atraso</h5>
                <div class="sx-muted small">Use uma solicitação existente para registrar atraso de viagem ou passageiro.</div>
            </div>
        </div>

        <form method="POST" action="" id="form-atraso-viagem" class="row g-2 align-items-end mb-3">
            @csrf
            <div class="col-md-4">
                <label class="sx-label">Viagem</label>
                <select id="solicitacao-viagem" class="form-select" required>
                    <option value="">Selecione a viagem</option>
                    @foreach($solicitacoes as $solicitacao)
                        <option value="{{ $solicitacao->id }}">#{{ $solicitacao->id }} - {{ $solicitacao->origem }} → {{ $solicitacao->destino }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="sx-label">Minutos</label>
                <input type="number" min="1" name="minutos_atraso" class="form-control" placeholder="Ex: 15" required>
            </div>
            <div class="col-md-4">
                <label class="sx-label">Motivo</label>
                <input type="text" name="motivo" class="form-control" placeholder="Motivo do atraso">
            </div>
            <div class="col-md-2">
                <button class="btn btn-systex w-100">Salvar atraso</button>
            </div>
        </form>

        <form method="POST" action="" id="form-atraso-passageiro" class="row g-2 align-items-end">
            @csrf
            <div class="col-md-4">
                <label class="sx-label">Viagem</label>
                <select id="solicitacao-passageiro" class="form-select" required>
                    <option value="">Selecione a viagem</option>
                    @foreach($solicitacoes as $solicitacao)
                        <option value="{{ $solicitacao->id }}">#{{ $solicitacao->id }} - {{ $solicitacao->origem }} → {{ $solicitacao->destino }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="sx-label">Passageiro ID</label>
                <input type="number" min="1" name="passageiro_id" class="form-control" placeholder="ID do passageiro" required>
            </div>
            <div class="col-md-2">
                <label class="sx-label">Minutos</label>
                <input type="number" min="1" name="minutos_atraso" class="form-control" placeholder="Ex: 10" required>
            </div>
            <div class="col-md-3">
                <button class="btn btn-outline-light w-100">Salvar atraso de passageiro</button>
            </div>
        </form>
    </div>

    <div class="sx-card mb-3">
        <div class="sx-card-header">
            <h5 class="sx-card-title">Atrasos de viagem</h5>
        </div>

        @if($atrasosViagem->count())
            <div class="table-responsive sx-table-shell">
                <table class="table table-hover table-systex-grid">
                    <thead>
                        <tr>
                            <th>Viagem</th>
                            <th>Cliente</th>
                            <th>Tempo</th>
                            <th>Motivo</th>
                            <th>Registrado em</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($atrasosViagem as $atraso)
                        <tr>
                            <td class="fw-semibold">#{{ $atraso->solicitacao_id }}</td>
                            <td>{{ $atraso->cliente->nome_fantasia ?? $atraso->cliente->razao_social ?? '-' }}</td>
                            <td><span class="sx-badge sx-badge-danger">{{ $atraso->minutos_atraso }} min</span></td>
                            <td class="sx-muted">{{ $atraso->motivo ?? '-' }}</td>
                            <td>{{ $atraso->created_at->format('d/m/Y H:i') }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        @else
            @include('partials.panel.empty-state', [
                'title' => 'Nenhum atraso de viagem registrado',
                'message' => 'Os atrasos de viagem aparecerão aqui para acompanhamento operacional.',
                'icon' => 'bi bi-clock-history',
            ])
        @endif

        <div class="d-flex justify-content-end mt-3">{{ $atrasosViagem->links() }}</div>
    </div>

    <div class="sx-card">
        <div class="sx-card-header">
            <h5 class="sx-card-title">Atrasos por passageiro</h5>
        </div>

        @if($atrasosPassageiro->count())
            <div class="table-responsive sx-table-shell">
                <table class="table table-hover table-systex-grid">
                    <thead>
                        <tr>
                            <th>Viagem</th>
                            <th>Passageiro</th>
                            <th>Tempo</th>
                            <th>Motivo</th>
                            <th>Registrado em</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($atrasosPassageiro as $atraso)
                        <tr>
                            <td class="fw-semibold">#{{ $atraso->solicitacao_id }}</td>
                            <td>{{ $atraso->passageiro->nome ?? '-' }}</td>
                            <td><span class="sx-badge sx-badge-warning">{{ $atraso->minutos_atraso }} min</span></td>
                            <td class="sx-muted">{{ $atraso->motivo ?? '-' }}</td>
                            <td>{{ $atraso->created_at->format('d/m/Y H:i') }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        @else
            @include('partials.panel.empty-state', [
                'title' => 'Nenhum atraso de passageiro registrado',
                'message' => 'Atrasos individuais aparecerão aqui quando forem informados pela operação.',
                'icon' => 'bi bi-person-exclamation',
            ])
        @endif

        <div class="d-flex justify-content-end mt-3">{{ $atrasosPassageiro->links() }}</div>
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
