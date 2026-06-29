@extends('layouts.app')

@section('title', 'Centro de Controle Operacional')

@section('page-heading')
<div class="cc-topbar">
    <div>
        <div class="cc-eyebrow"><span class="cc-live-dot"></span> Centro de Controle Operacional</div>
        <h2 class="cc-title">Olá, {{ Str::before(auth()->user()->name, ' ') }}.</h2>
        <p class="cc-subtitle">{{ now()->translatedFormat('l, d \d\e F \d\e Y') }} · Visão operacional de {{ strtolower($periodoLabel) }}</p>
    </div>
    <div class="cc-top-actions">
        <div class="cc-updated"><span>Última atualização</span><strong>{{ now()->format('H:i:s') }}</strong></div>
        <a href="{{ request()->fullUrl() }}" class="btn btn-outline-light"><i class="bi bi-arrow-clockwise"></i> Atualizar</a>
        <a href="{{ route('painel.operador.solicitacoes.create') }}" class="btn btn-systex"><i class="bi bi-plus-lg"></i> Nova viagem</a>
    </div>
</div>
@endsection

@section('content')
<div class="sx-container control-center">
    <form method="GET" class="cc-filter-bar" aria-label="Filtros do centro operacional">
        <div class="cc-filter-title"><i class="bi bi-sliders2"></i><span>Filtros rápidos</span></div>
        <div class="cc-filter-field">
            <label for="cliente_id">Cliente</label>
            <select id="cliente_id" name="cliente_id" class="form-select">
                <option value="">Todos os clientes</option>
                @foreach($clientes as $cliente)
                    <option value="{{ $cliente->id }}" @selected((string) request('cliente_id') === (string) $cliente->id)>{{ $cliente->nome_fantasia ?: $cliente->razao_social }}</option>
                @endforeach
            </select>
        </div>
        <div class="cc-filter-field">
            <label for="motorista_id">Motorista</label>
            <select id="motorista_id" name="motorista_id" class="form-select">
                <option value="">Todos os motoristas</option>
                @foreach($motoristas as $motorista)
                    <option value="{{ $motorista->id }}" @selected((string) request('motorista_id') === (string) $motorista->id)>{{ $motorista->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="cc-filter-field">
            <label for="periodo">Período</label>
            <select id="periodo" name="periodo" class="form-select">
                <option value="hoje" @selected(request('periodo', 'hoje') === 'hoje')>Hoje</option>
                <option value="7_dias" @selected(request('periodo') === '7_dias')>Próximos 7 dias</option>
                <option value="30_dias" @selected(request('periodo') === '30_dias')>Próximos 30 dias</option>
                <option value="todos" @selected(request('periodo') === 'todos')>Todo o período</option>
            </select>
        </div>
        <div class="cc-filter-field">
            <label for="situacao">Situação</label>
            <select id="situacao" name="situacao" class="form-select">
                <option value="">Todas as situações</option>
                @foreach($statusOptions as $valor => $rotulo)
                    <option value="{{ $valor }}" @selected(request('situacao') === $valor)>{{ $rotulo }}</option>
                @endforeach
            </select>
        </div>
        <button class="btn btn-systex cc-filter-button"><i class="bi bi-funnel"></i> Aplicar</button>
        @if(request()->hasAny(['cliente_id', 'motorista_id', 'periodo', 'situacao']))
            <a href="{{ route('painel.dashboard') }}" class="cc-clear-filter" title="Limpar filtros"><i class="bi bi-x-lg"></i></a>
        @endif
    </form>

    <section class="cc-attention" aria-labelledby="attention-title">
        <div class="cc-section-heading cc-attention-heading">
            <div>
                <span class="cc-section-kicker">Prioridade operacional</span>
                <h3 id="attention-title"><i class="bi bi-broadcast-pin"></i> Centro de Atenção</h3>
            </div>
            <span class="cc-attention-count">{{ count($alertas) }} {{ count($alertas) === 1 ? 'sinal' : 'sinais' }}</span>
        </div>
        <div class="cc-alert-grid">
            @foreach($alertas as $alerta)
                <a href="{{ $alerta['url'] }}" class="cc-alert cc-alert-{{ $alerta['nivel'] }}">
                    <span class="cc-alert-icon"><i class="bi {{ $alerta['icone'] }}"></i></span>
                    <span class="cc-alert-copy"><strong>{{ $alerta['titulo'] }}</strong><small>{{ $alerta['texto'] }}</small></span>
                    <i class="bi bi-arrow-up-right cc-alert-arrow"></i>
                </a>
            @endforeach
        </div>
    </section>

    @php
        $kpis = [
            ['label' => $periodoLabel, 'value' => $metricas['viagens_periodo'], 'icon' => 'bi-calendar2-week', 'tone' => 'neutral', 'hint' => ($tendenciaViagens >= 0 ? '+' : '').$tendenciaViagens.'% vs. ontem', 'url' => route('painel.operador.solicitacoes.index')],
            ['label' => 'Em andamento', 'value' => $metricas['em_andamento'], 'icon' => 'bi-play-circle', 'tone' => 'info', 'hint' => 'Agora', 'url' => route('painel.operador.solicitacoes.index', ['status' => 'em_andamento'])],
            ['label' => 'Programadas', 'value' => $metricas['programadas'], 'icon' => 'bi-calendar-check', 'tone' => 'info', 'hint' => 'No período', 'url' => route('painel.operador.solicitacoes.index', ['status' => 'programada'])],
            ['label' => 'Extras', 'value' => $metricas['extras'], 'icon' => 'bi-lightning-charge', 'tone' => 'warning', 'hint' => 'No período', 'url' => route('painel.operador.solicitacoes.index')],
            ['label' => 'Motoristas disponíveis', 'value' => $metricas['motoristas_disponiveis'], 'icon' => 'bi-person-check', 'tone' => 'success', 'hint' => 'Aptos para escala', 'url' => route('master.motoristas.index')],
            ['label' => 'Motoristas em viagem', 'value' => $metricas['motoristas_em_viagem'], 'icon' => 'bi-person-gear', 'tone' => 'info', 'hint' => 'Com atribuição ativa', 'url' => route('painel.operador.solicitacoes.index')],
            ['label' => 'Veículos disponíveis', 'value' => $metricas['veiculos_disponiveis'], 'icon' => 'bi-truck', 'tone' => 'success', 'hint' => 'Liberados e livres', 'url' => route('master.veiculos.index')],
            ['label' => 'Veículos bloqueados', 'value' => $metricas['veiculos_bloqueados'], 'icon' => 'bi-slash-circle', 'tone' => $metricas['veiculos_bloqueados'] ? 'danger' : 'success', 'hint' => $metricas['veiculos_bloqueados'] ? 'Requer ação' : 'Sem bloqueios', 'url' => route('master.veiculos.index')],
            ['label' => 'Checklists pendentes', 'value' => $metricas['checklists_pendentes'], 'icon' => 'bi-clipboard2-pulse', 'tone' => $metricas['checklists_pendentes'] ? 'warning' : 'success', 'hint' => 'Aguardando conclusão', 'url' => route('painel.operador.checklists.index')],
            ['label' => 'Ocorrências recentes', 'value' => $metricas['ocorrencias_recentes'], 'icon' => 'bi-exclamation-diamond', 'tone' => $metricas['ocorrencias_recentes'] ? 'danger' : 'success', 'hint' => 'Últimas 24 horas', 'url' => route('painel.operador.solicitacoes.index')],
            ['label' => 'Manutenções vencidas', 'value' => $metricas['manutencoes_vencidas'], 'icon' => 'bi-wrench-adjustable-circle', 'tone' => $metricas['manutencoes_vencidas'] ? 'danger' : 'success', 'hint' => 'Por KM ou data', 'url' => route('master.veiculos.index')],
            ['label' => 'Documentos em atenção', 'value' => $metricas['documentos_atencao'], 'icon' => 'bi-file-earmark-medical', 'tone' => $metricas['documentos_atencao'] ? 'warning' : 'success', 'hint' => 'CNHs vencidas ou próximas', 'url' => route('master.motoristas.index')],
        ];
    @endphp

    <section class="cc-section" aria-labelledby="kpi-title">
        <div class="cc-section-heading">
            <div><span class="cc-section-kicker">Pulso da operação</span><h3 id="kpi-title">Indicadores operacionais</h3></div>
        </div>
        <div class="cc-kpi-grid">
            @foreach($kpis as $kpi)
                <a href="{{ $kpi['url'] }}" class="cc-kpi cc-tone-{{ $kpi['tone'] }}">
                    <span class="cc-kpi-icon"><i class="bi {{ $kpi['icon'] }}"></i></span>
                    <span class="cc-kpi-value">{{ $kpi['value'] }}</span>
                    <span class="cc-kpi-label">{{ $kpi['label'] }}</span>
                    <span class="cc-kpi-hint"><i class="bi bi-graph-up-arrow"></i> {{ $kpi['hint'] }}</span>
                    <i class="bi bi-chevron-right cc-kpi-arrow"></i>
                </a>
            @endforeach
        </div>
    </section>

    <section class="cc-quick-strip" aria-label="Indicadores rápidos">
        <div class="cc-quick-group">
            <div class="cc-quick-title"><i class="bi bi-person-vcard"></i><span>CNH</span></div>
            <a href="{{ route('master.motoristas.index') }}"><strong class="text-danger">{{ $indicadores['cnh_vencidas'] }}</strong><small>Vencidas</small></a>
            <a href="{{ route('master.motoristas.index') }}"><strong class="text-warning">{{ $indicadores['cnh_proximas'] }}</strong><small>Próximas</small></a>
        </div>
        <div class="cc-quick-group">
            <div class="cc-quick-title"><i class="bi bi-truck"></i><span>Veículos</span></div>
            <a href="{{ route('master.veiculos.index') }}"><strong class="text-warning">{{ $indicadores['veiculos_manutencao'] }}</strong><small>Em atenção</small></a>
            <a href="{{ route('master.veiculos.index') }}"><strong class="text-danger">{{ $indicadores['veiculos_bloqueados'] }}</strong><small>Bloqueados</small></a>
            <a href="{{ route('master.veiculos.index') }}"><strong class="text-success">{{ $indicadores['veiculos_disponiveis'] }}</strong><small>Disponíveis</small></a>
        </div>
        <div class="cc-quick-group">
            <div class="cc-quick-title"><i class="bi bi-clipboard2-check"></i><span>Checklist</span></div>
            <a href="{{ route('painel.operador.checklists.index') }}"><strong class="text-warning">{{ $indicadores['checklists_pendentes'] }}</strong><small>Pendentes</small></a>
            <a href="{{ route('painel.operador.checklists.index') }}"><strong class="text-success">{{ $indicadores['checklists_hoje'] }}</strong><small>Concluídos hoje</small></a>
        </div>
    </section>

    <section class="cc-section" aria-labelledby="charts-title">
        <div class="cc-section-heading">
            <div><span class="cc-section-kicker">Leitura visual</span><h3 id="charts-title">Comportamento das viagens</h3></div>
        </div>
        <div class="cc-chart-grid">
            <article class="cc-panel cc-chart-wide"><div class="cc-panel-head"><div><h4>Viagens por hora</h4><p>Distribuição no período selecionado</p></div><i class="bi bi-bar-chart-line"></i></div><div class="cc-chart-wrap"><canvas id="chartHoras"></canvas></div></article>
            <article class="cc-panel"><div class="cc-panel-head"><div><h4>Programadas x Extras</h4><p>Natureza da demanda</p></div><i class="bi bi-pie-chart"></i></div><div class="cc-chart-wrap"><canvas id="chartNaturezas"></canvas></div></article>
            <article class="cc-panel cc-chart-wide"><div class="cc-panel-head"><div><h4>Viagens por cliente</h4><p>Clientes com maior volume</p></div><i class="bi bi-buildings"></i></div><div class="cc-chart-wrap"><canvas id="chartClientes"></canvas></div></article>
            <article class="cc-panel"><div class="cc-panel-head"><div><h4>Status das viagens</h4><p>Composição operacional</p></div><i class="bi bi-activity"></i></div><div class="cc-chart-wrap"><canvas id="chartStatus"></canvas></div></article>
        </div>
    </section>

    <section class="cc-section" aria-labelledby="trips-title">
        <div class="cc-section-heading">
            <div><span class="cc-section-kicker">Operação</span><h3 id="trips-title">Últimas viagens</h3></div>
            <a href="{{ route('painel.operador.solicitacoes.index') }}" class="btn btn-outline-light btn-sm">Ver todas <i class="bi bi-arrow-right"></i></a>
        </div>
        <div class="cc-panel cc-table-panel">
            @if($ultimasSolicitacoes->count())
                <div class="table-responsive">
                    <table class="table cc-table align-middle">
                        <thead><tr><th>ID</th><th>Cliente / Trajeto</th><th>Motorista</th><th>Veículo</th><th>Tipo</th><th>Natureza</th><th>Data / Hora</th><th>Status</th><th>Checklist</th><th></th></tr></thead>
                        <tbody>
                        @foreach($ultimasSolicitacoes as $solicitacao)
                            @php
                                $atribuicao = $solicitacao->atribuicoes->last();
                                $checklist = $solicitacao->checklists->last();
                            @endphp
                            <tr>
                                <td><span class="cc-trip-id">#{{ $solicitacao->id }}</span></td>
                                <td><strong>{{ $solicitacao->cliente->nome_fantasia ?? $solicitacao->cliente->razao_social ?? '-' }}</strong><small>{{ $solicitacao->origem }} <i class="bi bi-arrow-right"></i> {{ $solicitacao->destino }}</small></td>
                                <td>{{ $atribuicao?->motorista?->name ?? 'Não atribuído' }}</td>
                                <td>{{ $atribuicao?->veiculo?->placa ?? '—' }}</td>
                                <td>{{ ['diario' => 'Diário', 'mensal' => 'Mensal', 'esporadico' => 'Esporádico'][$solicitacao->tipo_periodo] ?? 'Esporádico' }}</td>
                                <td><span class="cc-nature cc-nature-{{ $solicitacao->natureza }}">{{ $solicitacao->natureza === 'extra' ? 'Extra' : 'Programada' }}</span></td>
                                <td><strong>{{ $solicitacao->data_hora?->format('d/m/Y') }}</strong><small>{{ $solicitacao->data_hora?->format('H:i') }}</small></td>
                                <td>@include('partials.panel.status-badge', ['status' => $solicitacao->status, 'label' => $solicitacao->statusLabel()])</td>
                                <td><span class="cc-check cc-check-{{ $checklist?->status ?? 'none' }}"><i class="bi {{ $checklist ? 'bi-clipboard-check' : 'bi-clipboard-x' }}"></i> {{ $checklist ? ucfirst(str_replace('_', ' ', $checklist->status)) : 'Não iniciado' }}</span></td>
                                <td><a href="{{ route('painel.operador.solicitacoes.show', $solicitacao->id) }}" class="cc-open-button" title="Abrir viagem"><i class="bi bi-arrow-up-right"></i></a></td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="cc-empty"><i class="bi bi-calendar2-x"></i><strong>Nenhuma viagem no período</strong><span>Ajuste os filtros ou crie uma nova viagem.</span></div>
            @endif
        </div>
    </section>

    <div class="cc-lower-grid">
        <section class="cc-panel" aria-labelledby="alerts-list-title">
            <div class="cc-panel-head"><div><span class="cc-section-kicker">Pendências</span><h4 id="alerts-list-title">Painel de alertas</h4></div><i class="bi bi-bell"></i></div>
            <div class="cc-list">
                @foreach($alertas as $alerta)
                    <a href="{{ $alerta['url'] }}" class="cc-list-item"><span class="cc-list-icon cc-bg-{{ $alerta['nivel'] }}"><i class="bi {{ $alerta['icone'] }}"></i></span><span><strong>{{ $alerta['titulo'] }}</strong><small>{{ $alerta['texto'] }}</small></span><i class="bi bi-chevron-right"></i></a>
                @endforeach
            </div>
        </section>

        <section class="cc-panel" aria-labelledby="occurrences-title">
            <div class="cc-panel-head"><div><span class="cc-section-kicker">Últimos registros</span><h4 id="occurrences-title">Ocorrências recentes</h4></div><i class="bi bi-exclamation-octagon"></i></div>
            @if($ocorrenciasRecentes->count())
                <div class="cc-list">
                    @foreach($ocorrenciasRecentes as $ocorrencia)
                        @php($atribuicaoOcorrencia = $ocorrencia->solicitacao?->atribuicoes?->last())
                        <a href="{{ route('painel.operador.solicitacoes.show', $ocorrencia->solicitacao_id) }}" class="cc-occurrence">
                            <time>{{ ($ocorrencia->registrado_em ?: $ocorrencia->created_at)?->format('d/m · H:i') }}</time>
                            <span><strong>{{ $ocorrencia->tipo }}</strong><small>Viagem #{{ $ocorrencia->solicitacao_id }} · {{ $atribuicaoOcorrencia?->motorista?->name ?? 'Sem motorista' }}</small><em>{{ Str::limit($ocorrencia->descricao, 82) }}</em></span>
                            <span class="cc-occurrence-status">Registrada</span>
                        </a>
                    @endforeach
                </div>
            @else
                <div class="cc-empty cc-empty-small"><i class="bi bi-shield-check"></i><strong>Nenhuma ocorrência recente</strong><span>A operação não possui registros de ocorrência.</span></div>
            @endif
        </section>

        <section class="cc-panel cc-timeline-panel" aria-labelledby="timeline-title">
            <div class="cc-panel-head"><div><span class="cc-section-kicker">Eventos</span><h4 id="timeline-title">Timeline operacional</h4></div><i class="bi bi-clock-history"></i></div>
            <div class="cc-timeline">
                @forelse($timeline as $evento)
                    <a href="{{ $evento['url'] }}" class="cc-timeline-item"><time>{{ Carbon\Carbon::parse($evento['data'])->format('H:i') }}<small>{{ Carbon\Carbon::parse($evento['data'])->format('d/m') }}</small></time><span class="cc-timeline-marker"><i class="bi {{ $evento['icone'] }}"></i></span><span class="cc-timeline-copy"><strong>{{ $evento['titulo'] }}</strong><small>{{ $evento['texto'] }}</small></span></a>
                @empty
                    <div class="cc-empty cc-empty-small"><i class="bi bi-clock"></i><strong>Sem eventos recentes</strong></div>
                @endforelse
            </div>
        </section>
    </div>

    <section class="cc-section" aria-labelledby="quick-actions-title">
        <div class="cc-section-heading"><div><span class="cc-section-kicker">Atalhos</span><h3 id="quick-actions-title">Ações rápidas</h3></div></div>
        <div class="cc-actions-grid">
            <a href="{{ route('painel.operador.solicitacoes.create') }}"><i class="bi bi-sign-turn-right"></i><span><strong>Nova viagem</strong><small>Programar operação</small></span></a>
            <a href="{{ route('master.motoristas.create') }}"><i class="bi bi-person-plus"></i><span><strong>Novo motorista</strong><small>Cadastrar profissional</small></span></a>
            <a href="{{ route('master.veiculos.create') }}"><i class="bi bi-truck"></i><span><strong>Novo veículo</strong><small>Adicionar à frota</small></span></a>
            <a href="{{ route('painel.operador.solicitacoes.index') }}"><i class="bi bi-exclamation-diamond"></i><span><strong>Nova ocorrência</strong><small>Abrir pela viagem</small></span></a>
            <a href="{{ route('checklists.create') }}"><i class="bi bi-clipboard2-plus"></i><span><strong>Novo checklist</strong><small>Iniciar inspeção</small></span></a>
        </div>
    </section>
</div>

<style>
.control-center{--cc-surface:rgba(18,18,21,.86);--cc-raised:rgba(24,24,28,.94);--cc-border:rgba(255,255,255,.085);--cc-muted:rgba(255,255,255,.56);--cc-red:#ff3636;--cc-orange:#fb923c;--cc-yellow:#facc15;--cc-green:#34d399;--cc-blue:#60a5fa;padding-bottom:42px}.cc-topbar{width:100%;display:flex;align-items:center;justify-content:space-between;gap:24px}.cc-eyebrow,.cc-section-kicker{font-size:11px;text-transform:uppercase;letter-spacing:.14em;font-weight:800;color:rgba(255,255,255,.5)}.cc-live-dot{display:inline-block;width:8px;height:8px;border-radius:50%;background:var(--success);box-shadow:0 0 0 5px rgba(34,197,94,.12);margin-right:8px}.cc-title{color:#fff;font-weight:850;margin:5px 0 2px;font-size:clamp(1.55rem,2.3vw,2.15rem)}.cc-subtitle{color:rgba(255,255,255,.58);margin:0;text-transform:capitalize}.cc-top-actions{display:flex;align-items:center;gap:10px}.cc-updated{display:flex;flex-direction:column;align-items:flex-end;padding-right:10px}.cc-updated span{font-size:10px;text-transform:uppercase;letter-spacing:.1em;color:var(--cc-muted)}.cc-updated strong{color:#fff;font-variant-numeric:tabular-nums}.cc-filter-bar{display:grid;grid-template-columns:auto repeat(4,minmax(145px,1fr)) auto auto;gap:12px;align-items:end;background:var(--cc-surface);border:1px solid var(--cc-border);border-radius:18px;padding:15px 16px;margin-bottom:20px;box-shadow:0 18px 45px rgba(0,0,0,.22)}.cc-filter-title{height:42px;display:flex;align-items:center;gap:8px;color:#fff;font-weight:750;padding-right:8px}.cc-filter-title i{color:var(--cc-red);font-size:18px}.cc-filter-field label{display:block;color:var(--cc-muted);font-size:10px;text-transform:uppercase;letter-spacing:.08em;font-weight:750;margin-bottom:5px}.cc-filter-field .form-select{height:42px;background-color:rgba(255,255,255,.045)!important;border-color:var(--cc-border)!important;font-size:13px}.cc-filter-button{height:42px}.cc-clear-filter{width:42px;height:42px;border:1px solid var(--cc-border);display:grid;place-items:center;border-radius:12px;color:var(--cc-muted)}.cc-attention{position:relative;overflow:hidden;background:linear-gradient(130deg,rgba(93,20,24,.52),rgba(25,18,21,.96) 44%,rgba(18,18,21,.96));border:1px solid rgba(255,70,70,.22);border-radius:22px;padding:20px;margin-bottom:26px;box-shadow:0 22px 60px rgba(0,0,0,.28),inset 0 1px rgba(255,255,255,.04)}.cc-attention:before{content:"";position:absolute;width:300px;height:300px;border-radius:50%;background:rgba(255,54,54,.1);filter:blur(60px);left:-100px;top:-190px}.cc-section-heading,.cc-panel-head{display:flex;justify-content:space-between;align-items:center;gap:14px}.cc-section-heading h3,.cc-panel-head h4{color:#fff;margin:3px 0 0;font-weight:800}.cc-attention-heading{position:relative;margin-bottom:15px}.cc-attention-heading h3 i{color:var(--cc-red);margin-right:7px}.cc-attention-count{font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:.08em;background:rgba(255,255,255,.06);border:1px solid var(--cc-border);border-radius:999px;padding:7px 10px;color:rgba(255,255,255,.7)}.cc-alert-grid{position:relative;display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:10px}.cc-alert{display:grid;grid-template-columns:42px 1fr auto;align-items:center;gap:11px;padding:12px;border:1px solid var(--cc-border);border-radius:15px;background:rgba(255,255,255,.035);transition:.2s}.cc-alert:hover{transform:translateY(-2px);background:rgba(255,255,255,.06)}.cc-alert-icon{width:42px;height:42px;display:grid;place-items:center;border-radius:13px;font-size:19px}.cc-alert-danger .cc-alert-icon,.cc-bg-danger{background:rgba(255,54,54,.13);color:#ff6262}.cc-alert-warning .cc-alert-icon,.cc-bg-warning{background:rgba(250,204,21,.12);color:var(--cc-yellow)}.cc-alert-success .cc-alert-icon,.cc-bg-success{background:rgba(52,211,153,.12);color:var(--cc-green)}.cc-alert-copy{display:flex;flex-direction:column;min-width:0}.cc-alert-copy strong{color:#fff;font-size:13px}.cc-alert-copy small{color:var(--cc-muted);font-size:11px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}.cc-alert-arrow{color:rgba(255,255,255,.32)}.cc-section{margin-top:28px}.cc-section>.cc-section-heading{margin-bottom:13px}.cc-kpi-grid{display:grid;grid-template-columns:repeat(6,minmax(0,1fr));gap:11px}.cc-kpi{position:relative;display:flex;flex-direction:column;min-height:154px;padding:15px;border-radius:17px;background:var(--cc-surface);border:1px solid var(--cc-border);overflow:hidden;transition:.2s}.cc-kpi:before{content:"";position:absolute;left:0;top:0;bottom:0;width:3px;background:var(--tone,var(--cc-blue))}.cc-kpi:hover{transform:translateY(-3px);border-color:color-mix(in srgb,var(--tone) 42%,transparent);box-shadow:0 18px 38px rgba(0,0,0,.24)}.cc-tone-danger{--tone:var(--cc-red)}.cc-tone-warning{--tone:var(--cc-yellow)}.cc-tone-success{--tone:var(--cc-green)}.cc-tone-info{--tone:var(--cc-blue)}.cc-tone-neutral{--tone:#a78bfa}.cc-kpi-icon{width:34px;height:34px;display:grid;place-items:center;border-radius:11px;background:color-mix(in srgb,var(--tone) 12%,transparent);color:var(--tone)}.cc-kpi-value{font-size:29px;line-height:1;color:#fff;font-weight:850;margin:13px 0 6px}.cc-kpi-label{font-size:12px;color:rgba(255,255,255,.78);font-weight:700}.cc-kpi-hint{font-size:10px;color:var(--cc-muted);margin-top:auto;padding-top:9px}.cc-kpi-hint i{color:var(--tone)}.cc-kpi-arrow{position:absolute;right:13px;top:14px;color:rgba(255,255,255,.2)}.cc-quick-strip{display:grid;grid-template-columns:1fr 1.35fr 1fr;gap:1px;margin-top:16px;background:var(--cc-border);border:1px solid var(--cc-border);border-radius:17px;overflow:hidden}.cc-quick-group{display:flex;align-items:center;justify-content:space-around;gap:12px;background:var(--cc-surface);padding:13px 16px}.cc-quick-title{display:flex;align-items:center;gap:8px;color:#fff;font-size:12px;font-weight:800}.cc-quick-title i{color:var(--cc-red);font-size:17px}.cc-quick-group>a{display:flex;align-items:baseline;gap:6px}.cc-quick-group strong{font-size:18px}.cc-quick-group small{color:var(--cc-muted);font-size:10px}.cc-chart-grid{display:grid;grid-template-columns:1.45fr 1fr;gap:12px}.cc-panel{background:var(--cc-surface);border:1px solid var(--cc-border);border-radius:19px;padding:17px;box-shadow:0 16px 42px rgba(0,0,0,.18)}.cc-panel-head{margin-bottom:12px}.cc-panel-head h4{font-size:15px}.cc-panel-head p{font-size:11px;color:var(--cc-muted);margin:2px 0 0}.cc-panel-head>i{font-size:19px;color:rgba(255,255,255,.32)}.cc-chart-wrap{position:relative;height:245px}.cc-table-panel{padding:0;overflow:hidden}.cc-table{margin:0;color:#fff}.cc-table thead th{padding:13px 12px;background:rgba(255,255,255,.025)!important;color:rgba(255,255,255,.42)!important;border-color:var(--cc-border)!important;font-size:9px;text-transform:uppercase;letter-spacing:.08em;white-space:nowrap}.cc-table tbody td{padding:13px 12px;color:rgba(255,255,255,.73)!important;border-color:rgba(255,255,255,.055)!important;font-size:11px;white-space:nowrap}.cc-table tbody tr:hover td{background:rgba(255,255,255,.025)!important}.cc-table td strong,.cc-table td small{display:block}.cc-table td strong{color:#fff;font-size:11px}.cc-table td small{font-size:10px;color:var(--cc-muted);margin-top:3px}.cc-trip-id{font-weight:850;color:#fff}.cc-nature,.cc-check{display:inline-flex;align-items:center;gap:5px;padding:5px 7px;border-radius:7px;font-size:9px;font-weight:800}.cc-nature-programada{background:rgba(96,165,250,.12);color:#8cbcff}.cc-nature-extra{background:rgba(250,204,21,.12);color:#fbdc55}.cc-check-finalizado,.cc-check-concluido{background:rgba(52,211,153,.12);color:#6ee7b7}.cc-check-none{background:rgba(255,255,255,.06);color:var(--cc-muted)}.cc-open-button{width:31px;height:31px;display:grid;place-items:center;border:1px solid var(--cc-border);border-radius:9px;color:#fff}.cc-open-button:hover{background:var(--cc-red);border-color:var(--cc-red)}.cc-lower-grid{display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px;margin-top:28px}.cc-list{display:flex;flex-direction:column}.cc-list-item{display:grid;grid-template-columns:38px 1fr auto;align-items:center;gap:10px;padding:10px 0;border-bottom:1px solid rgba(255,255,255,.055)}.cc-list-item:last-child{border-bottom:0}.cc-list-icon{width:38px;height:38px;display:grid;place-items:center;border-radius:11px}.cc-list-item>span:nth-child(2){display:flex;flex-direction:column;min-width:0}.cc-list-item strong{color:#fff;font-size:11px}.cc-list-item small{color:var(--cc-muted);font-size:10px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}.cc-list-item>i{color:rgba(255,255,255,.22)}.cc-occurrence{display:grid;grid-template-columns:66px 1fr auto;gap:10px;align-items:start;padding:11px 0;border-bottom:1px solid rgba(255,255,255,.055)}.cc-occurrence time{font-size:9px;color:var(--cc-muted)}.cc-occurrence>span:nth-child(2){display:flex;flex-direction:column}.cc-occurrence strong{color:#fff;font-size:11px}.cc-occurrence small,.cc-occurrence em{font-size:9px;color:var(--cc-muted);font-style:normal}.cc-occurrence em{margin-top:4px}.cc-occurrence-status{font-size:8px;color:#fbdc55;background:rgba(250,204,21,.1);padding:4px 6px;border-radius:6px}.cc-timeline{position:relative}.cc-timeline:before{content:"";position:absolute;left:55px;top:10px;bottom:10px;width:1px;background:rgba(255,255,255,.08)}.cc-timeline-item{position:relative;display:grid;grid-template-columns:42px 28px 1fr;gap:8px;align-items:center;padding:7px 0}.cc-timeline-item time{font-size:11px;font-weight:800;color:#fff;text-align:right}.cc-timeline-item time small{display:block;font-size:8px;color:var(--cc-muted);font-weight:500}.cc-timeline-marker{position:relative;width:28px;height:28px;display:grid;place-items:center;border-radius:50%;background:#25252a;border:1px solid rgba(255,255,255,.1);color:var(--cc-red);font-size:11px}.cc-timeline-copy{display:flex;flex-direction:column;min-width:0}.cc-timeline-copy strong{color:#fff;font-size:10px}.cc-timeline-copy small{font-size:9px;color:var(--cc-muted);white-space:nowrap;overflow:hidden;text-overflow:ellipsis}.cc-actions-grid{display:grid;grid-template-columns:repeat(5,1fr);gap:10px}.cc-actions-grid>a{display:flex;align-items:center;gap:12px;padding:15px;border:1px solid var(--cc-border);border-radius:15px;background:var(--cc-surface);transition:.2s}.cc-actions-grid>a:hover{transform:translateY(-2px);border-color:rgba(255,54,54,.3);background:rgba(255,54,54,.055)}.cc-actions-grid>a>i{width:38px;height:38px;display:grid;place-items:center;border-radius:11px;background:rgba(255,54,54,.1);color:var(--cc-red);font-size:17px}.cc-actions-grid span{display:flex;flex-direction:column}.cc-actions-grid strong{color:#fff;font-size:11px}.cc-actions-grid small{color:var(--cc-muted);font-size:9px}.cc-empty{min-height:180px;display:flex;flex-direction:column;justify-content:center;align-items:center;color:var(--cc-muted);gap:5px}.cc-empty i{font-size:27px;color:rgba(255,255,255,.2)}.cc-empty strong{color:#fff}.cc-empty-small{min-height:190px}.text-danger{color:#ff6262!important}.text-warning{color:#fbdc55!important}.text-success{color:#6ee7b7!important}
@media(max-width:1400px){.cc-kpi-grid{grid-template-columns:repeat(4,1fr)}.cc-filter-bar{grid-template-columns:repeat(4,1fr)}.cc-filter-title{grid-column:1/-1;height:auto}.cc-alert-grid{grid-template-columns:repeat(2,1fr)}}
@media(max-width:1100px){.cc-kpi-grid{grid-template-columns:repeat(3,1fr)}.cc-lower-grid{grid-template-columns:1fr 1fr}.cc-timeline-panel{grid-column:1/-1}.cc-actions-grid{grid-template-columns:repeat(3,1fr)}.cc-quick-strip{grid-template-columns:1fr}.cc-chart-grid{grid-template-columns:1fr}}
@media(max-width:767px){.cc-topbar{align-items:flex-start;flex-direction:column}.cc-top-actions{width:100%;flex-wrap:wrap}.cc-updated{align-items:flex-start;margin-right:auto}.cc-filter-bar{grid-template-columns:1fr 1fr}.cc-filter-title{grid-column:1/-1}.cc-filter-button{grid-column:1/-1}.cc-alert-grid,.cc-lower-grid{grid-template-columns:1fr}.cc-kpi-grid{grid-template-columns:repeat(2,1fr)}.cc-actions-grid{grid-template-columns:1fr 1fr}.cc-attention{padding:15px}.cc-quick-group{justify-content:flex-start;flex-wrap:wrap}.cc-quick-title{width:100%}}
@media(max-width:480px){.cc-filter-bar,.cc-kpi-grid,.cc-actions-grid{grid-template-columns:1fr}.cc-alert{grid-template-columns:38px 1fr auto}.cc-kpi{min-height:135px}.cc-top-actions .btn{flex:1}.cc-updated{width:100%}}
</style>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    if (typeof Chart === 'undefined') return;
    const charts = @json($graficos);
    Chart.defaults.color = 'rgba(255,255,255,.58)';
    Chart.defaults.font.family = 'Inter, system-ui, sans-serif';
    const grid = 'rgba(255,255,255,.055)';
    const tooltip = {backgroundColor:'#19191d',borderColor:'rgba(255,255,255,.1)',borderWidth:1,titleColor:'#fff',bodyColor:'rgba(255,255,255,.7)',padding:11};
    new Chart(document.getElementById('chartHoras'), {type:'bar',data:{labels:charts.horas.labels,datasets:[{label:'Viagens',data:charts.horas.data,backgroundColor:'rgba(255,54,54,.72)',borderRadius:5,borderSkipped:false}]},options:{maintainAspectRatio:false,plugins:{legend:{display:false},tooltip},scales:{x:{grid:{display:false},ticks:{maxTicksLimit:12}},y:{beginAtZero:true,grid:{color:grid},ticks:{precision:0}}}}});
    new Chart(document.getElementById('chartNaturezas'), {type:'doughnut',data:{labels:['Programadas','Extras'],datasets:[{data:[charts.naturezas.programadas,charts.naturezas.extras],backgroundColor:['#60a5fa','#facc15'],borderColor:'#18181c',borderWidth:5,hoverOffset:4}]},options:{maintainAspectRatio:false,cutout:'70%',plugins:{legend:{position:'bottom',labels:{usePointStyle:true,padding:16}},tooltip}}});
    new Chart(document.getElementById('chartClientes'), {type:'bar',data:{labels:charts.clientes.labels,datasets:[{label:'Viagens',data:charts.clientes.data,backgroundColor:'rgba(96,165,250,.7)',borderRadius:5,borderSkipped:false}]},options:{indexAxis:'y',maintainAspectRatio:false,plugins:{legend:{display:false},tooltip},scales:{x:{beginAtZero:true,grid:{color:grid},ticks:{precision:0}},y:{grid:{display:false}}}}});
    new Chart(document.getElementById('chartStatus'), {type:'doughnut',data:{labels:charts.status.labels,datasets:[{data:charts.status.data,backgroundColor:['#60a5fa','#a78bfa','#34d399','#facc15','#fb923c','#ff5252','#64748b'],borderColor:'#18181c',borderWidth:4}]},options:{maintainAspectRatio:false,cutout:'67%',plugins:{legend:{position:'bottom',labels:{usePointStyle:true,boxWidth:8,padding:12,font:{size:9}}},tooltip}}});
});
</script>
@endpush
