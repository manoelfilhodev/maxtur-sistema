@extends('layouts.app')

@section('page-heading')
    @include('partials.panel.page-header', [
        'title' => 'Viagem #'.$solicitacao->id,
        'subtitle' => 'Acompanhamento, atribuição e registros operacionais',
        'backRoute' => route('painel.operador.solicitacoes.index'),
    ])
@endsection

@section('content')
    <div class="sx-container">
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger">
            @foreach($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    @php
        $atribuicaoAtual = $solicitacao->atribuicoes->last();
    @endphp

    <div class="row g-3">
        <div class="col-lg-8">
            <div class="card mb-3">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between flex-wrap gap-2">
                        <div>
                            <h5 class="mb-1">Dados da viagem</h5>
                            <div class="text-muted">{{ optional($solicitacao->data_hora)->format('d/m/Y H:i') }}</div>
                        </div>
                        @include('partials.panel.status-badge', ['status' => $solicitacao->status, 'label' => $solicitacao->statusLabel()])
                    </div>
                    <hr>
                    <div class="row g-3">
                        <div class="col-md-6"><strong>Cliente:</strong><br>{{ $solicitacao->cliente->nome_fantasia ?? $solicitacao->cliente->razao_social ?? '-' }}</div>
                        <div class="col-md-3"><strong>Passageiros previstos:</strong><br>{{ $solicitacao->passageiros_previstos }}</div>
                        <div class="col-md-3"><strong>Passageiros vinculados:</strong><br>{{ $solicitacao->passageiros->count() }}</div>
                        <div class="col-md-6"><strong>Origem:</strong><br>{{ $solicitacao->origem }}</div>
                        <div class="col-md-6"><strong>Destino:</strong><br>{{ $solicitacao->destino }}</div>
                        <div class="col-12"><strong>Observação:</strong><br>{{ $solicitacao->observacao ?: '-' }}</div>
                    </div>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-body">
                    <h5>Passageiros</h5>
                    <div class="table-responsive">
                        <table class="table table-dark table-striped mb-0">
                            <tbody>
                                @forelse($solicitacao->passageiros as $passageiro)
                                    <tr><td>{{ $passageiro->nome }}</td></tr>
                                @empty
                                    <tr><td>Sem passageiros vinculados.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-body">
                    <h5>Checklist</h5>
                    <div class="table-responsive">
                        <table class="table table-dark table-striped mb-0">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Status</th>
                                    <th>Resultado</th>
                                    <th>Finalizado em</th>
                                    <th>Itens respondidos</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($solicitacao->checklists as $checklist)
                                    <tr>
                                        <td>#{{ $checklist->id }}</td>
                                        <td>{{ $checklist->status }}</td>
                                        <td>{{ $checklist->resultado ?: '-' }}</td>
                                        <td>{{ optional($checklist->finished_at)->format('d/m/Y H:i') ?: '-' }}</td>
                                        <td>{{ $checklist->respostas->count() }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5">Checklist ainda não respondido.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-body">
                    <h5>Atrasos e ocorrências</h5>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <h6>Atrasos</h6>
                            <ul class="mb-0">
                                @forelse($solicitacao->atrasosViagem as $atraso)
                                    <li>
                                        {{ $atraso->minutos_atraso }} min - {{ $atraso->motivo ?: 'Sem motivo informado' }}
                                        <small class="d-block text-muted">Ocorrido em {{ ($atraso->ocorrido_em ?? $atraso->created_at)->format('d/m/Y H:i') }} · registrado em {{ $atraso->created_at->format('d/m/Y H:i') }}</small>
                                    </li>
                                @empty
                                    <li>Nenhum atraso registrado.</li>
                                @endforelse
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6>Ocorrências</h6>
                            <ul class="mb-0">
                                @forelse($solicitacao->ocorrencias as $ocorrencia)
                                    <li><strong>{{ $ocorrencia->tipo }}</strong> - {{ $ocorrencia->descricao }}<small class="d-block text-muted">Ocorrido em {{ ($ocorrencia->ocorrido_em ?? $ocorrencia->registrado_em ?? $ocorrencia->created_at)->format('d/m/Y H:i') }} · registrado em {{ $ocorrencia->created_at->format('d/m/Y H:i') }}</small></li>
                                @empty
                                    <li>Nenhuma ocorrência registrada.</li>
                                @endforelse
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card mb-3">
                <div class="card-body">
                    <h5>Status operacional</h5>
                    <form method="POST" action="{{ route('painel.operador.solicitacoes.status', $solicitacao->id) }}" class="d-grid gap-2">
                        @csrf
                        @method('PATCH')
                        <select name="status" class="form-select" required>
                            @foreach($statusOptions as $status => $label)
                                <option value="{{ $status }}" @selected($solicitacao->status === $status)>{{ $label }}</option>
                            @endforeach
                        </select>
                        <button class="btn btn-systex">Atualizar status</button>
                    </form>
                    <div class="small sx-muted mt-2">
                        Use este controle na demo para aprovar, programar, iniciar ou finalizar a viagem.
                    </div>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-body">
                    <h5>Programação</h5>
                    @if($atribuicaoAtual)
                        <div class="mb-3">
                            <div><strong>Veículo:</strong> {{ $atribuicaoAtual->veiculo->placa ?? '-' }} - {{ $atribuicaoAtual->veiculo->modelo ?? '-' }}</div>
                            <div><strong>Motorista:</strong> {{ $atribuicaoAtual->motorista->name ?? '-' }}</div>
                            <div><strong>Atribuído em:</strong> {{ optional($atribuicaoAtual->atribuido_em)->format('d/m/Y H:i') }}</div>
                        </div>
                    @else
                        <p class="text-muted">Nenhum veículo ou motorista atribuído.</p>
                    @endif

                    <form method="POST" action="{{ route('painel.operador.solicitacoes.atribuir', $solicitacao->id) }}" class="d-grid gap-2">
                        @csrf
                        @method('PATCH')
                        <select name="veiculo_id" class="form-select" required>
                            <option value="">Selecione veículo</option>
                            @foreach($veiculos as $veiculo)
                                <option value="{{ $veiculo->id }}">
                                    {{ $veiculo->placa }} - {{ $veiculo->modelo }} | {{ $veiculo->capacidade_passageiros }} lugares | {{ $veiculo->status_operacional }}
                                </option>
                            @endforeach
                        </select>
                        <select name="motorista_id" class="form-select" required>
                            <option value="">Selecione motorista</option>
                            @foreach($motoristas as $motorista)
                                <option value="{{ $motorista->id }}">{{ $motorista->name }}{{ $motorista->ativo ? '' : ' (inativo)' }}</option>
                            @endforeach
                        </select>
                        <button class="btn btn-systex">Atribuir veículo e motorista</button>
                    </form>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-body">
                    <h5>Registrar atraso</h5>
                    <form method="POST" action="{{ route('painel.operador.atrasos.viagem.store', $solicitacao->id) }}" class="d-grid gap-2">
                        @csrf
                        <label class="form-label mb-0">Data da ocorrência</label>
                        <input type="date" name="data_ocorrencia" value="{{ old('data_ocorrencia', now()->format('Y-m-d')) }}" class="form-control" required>
                        <label class="form-label mb-0">Hora da ocorrência</label>
                        <input type="time" name="hora_ocorrencia" value="{{ old('hora_ocorrencia', now()->format('H:i')) }}" class="form-control" required>
                        <input type="number" min="1" name="minutos_atraso" class="form-control" placeholder="Minutos de atraso" required>
                        <textarea name="motivo" class="form-control" rows="3" placeholder="Motivo"></textarea>
                        <button class="btn btn-outline-light">Salvar atraso</button>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <h5>Registrar ocorrência</h5>
                    <form method="POST" action="{{ route('painel.operador.ocorrencias.store', $solicitacao->id) }}" class="d-grid gap-2">
                        @csrf
                        <label class="form-label mb-0">Data da ocorrência</label>
                        <input type="date" name="data_ocorrencia" value="{{ old('data_ocorrencia', now()->format('Y-m-d')) }}" class="form-control" required>
                        <label class="form-label mb-0">Hora da ocorrência</label>
                        <input type="time" name="hora_ocorrencia" value="{{ old('hora_ocorrencia', now()->format('H:i')) }}" class="form-control" required>
                        <input type="text" name="tipo" class="form-control" placeholder="Tipo da ocorrência" required>
                        <textarea name="descricao" class="form-control" rows="3" placeholder="Descrição" required></textarea>
                        <button class="btn btn-outline-light">Salvar ocorrência</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    </div>
@endsection
