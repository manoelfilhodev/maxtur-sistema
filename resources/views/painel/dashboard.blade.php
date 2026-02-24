@extends('layouts.app')

@section('page-heading')
    <div class="d-flex align-items-start justify-content-between flex-wrap gap-2">
        <div>
            <h3 class="fw-bold mb-1 text-white">Painel de Controle</h3>
            <div class="text-muted" style="color: rgba(255,255,255,.65) !important;">
                Visão geral da operação • últimos movimentos e tendências
            </div>
        </div>

        <div class="d-flex gap-2">
            <a href="{{ route('painel.relatorios.index') }}" class="btn btn-outline-light btn-sm">
    <i class="fa fa-chart-line me-1"></i> Relatórios
</a>

<a href="" class="btn btn-systex btn-sm">
    <i class="fa fa-clock me-1"></i> Ver registros
</a>
        </div>
    </div>
@endsection

@section('content')
    <style>
        /* Cards dark padrão SYSTEX */
        .dash-card {
            background: rgba(18, 18, 20, .72) !important;
            border: 1px solid rgba(255, 255, 255, .08) !important;
            border-radius: 16px !important;
            box-shadow: 0 16px 45px rgba(0, 0, 0, .35);
            color: #fff;
        }

        .dash-card .muted {
            color: rgba(255, 255, 255, .62);
        }

        .metric-value {
            font-size: 30px;
            font-weight: 900;
            letter-spacing: .2px;
            line-height: 1;
        }

        .metric-icon {
            width: 42px;
            height: 42px;
            border-radius: 14px;
            display: grid;
            place-items: center;
            background: rgba(255, 42, 42, .12);
            border: 1px solid rgba(255, 42, 42, .25);
            box-shadow: 0 12px 28px rgba(255, 42, 42, .12);
        }

        .metric-icon i {
            color: #ff2a2a;
        }

        .dash-table {
            border-radius: 16px;
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, .08);
        }

        .dash-table thead th {
            background: rgba(255, 255, 255, .04) !important;
            color: rgba(255, 255, 255, .75) !important;
            border-bottom: 1px solid rgba(255, 255, 255, .08) !important;
        }

        .dash-table tbody td {
            color: rgba(255, 255, 255, .85) !important;
            border-color: rgba(255, 255, 255, .06) !important;
        }

        .empty-state {
            padding: 18px;
            border-radius: 14px;
            border: 1px dashed rgba(255, 255, 255, .14);
            background: rgba(255, 255, 255, .03);
            color: rgba(255, 255, 255, .75);
        }

        /* badge “tipo de batida” (caso você use) */
        .badge-soft {
            border-radius: 999px;
            padding: 6px 10px;
            font-weight: 700;
            font-size: 12px;
            border: 1px solid rgba(255, 255, 255, .10);
        }

        .badge-entrada { background: rgba(34,197,94,.12); color: #b6f7cc; border-color: rgba(34,197,94,.20); }
        .badge-intervalo { background: rgba(250,204,21,.12); color: #ffeaa6; border-color: rgba(250,204,21,.22); }
        .badge-retorno { background: rgba(59,130,246,.12); color: #cfe2ff; border-color: rgba(59,130,246,.22); }
        .badge-saida { background: rgba(255,77,79,.12); color: #ffd0d0; border-color: rgba(255,77,79,.22); }
    </style>

    {{-- ================= METRICS (linha principal) ================= --}}
    <div class="row g-3 mb-3">
        <div class="col-xl-4 col-md-6">
            <div class="dash-card p-3">
                <div class="d-flex align-items-start justify-content-between">
                    <div>
                        <div class="muted small">Batidas hoje</div>
                        <div class="metric-value mt-1">0</div>
                        <div class="muted small mt-2">Movimentos registrados no dia</div>
                    </div>
                    <div class="metric-icon">
                        <i class="fa-solid fa-fingerprint"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-md-6">
            <div class="dash-card p-3">
                <div class="d-flex align-items-start justify-content-between">
                    <div>
                        <div class="muted small">Usuários ativos</div>
                        <div class="metric-value mt-1">{{ $usuariosAtivos }}</div>
                        <div class="muted small mt-2">Usuários cadastrados e ativos</div>
                    </div>
                    <div class="metric-icon">
                        <i class="fa-solid fa-users"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-md-12">
            <div class="dash-card p-3">
                <div class="d-flex align-items-start justify-content-between">
                    <div>
                        <div class="muted small">Registros no mês</div>
                        <div class="metric-value mt-1">0</div>
                        <div class="muted small mt-2">Volume acumulado do mês</div>
                    </div>
                    <div class="metric-icon">
                        <i class="fa-solid fa-chart-line"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ================= GRID PRINCIPAL (gráfico + ações) ================= --}}
    <div class="row g-3 mb-3">
        <div class="col-xl-8">
            <div class="dash-card p-3 h-100">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <div>
                        <div class="fw-bold">Registros por dia</div>
                        <div class="muted small">Últimos 7 dias (tendência rápida)</div>
                    </div>
                </div>

                @if(!empty($chartLabels ?? null) && !empty($chartValues ?? null))
                    <canvas id="chartRegistros" height="110"></canvas>
                @else
                    <div class="empty-state">
                        <div class="fw-bold mb-1">Gráfico ainda não configurado</div>
                        <div class="small">
                            Quando você enviar <code>$chartLabels</code> e <code>$chartValues</code> pelo controller,
                            esse gráfico aparece automaticamente.
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <div class="col-xl-4">
            <div class="dash-card p-3 h-100">
                <div class="fw-bold mb-2">Ações rápidas</div>

                <div class="d-grid gap-2">
                    <a href="" class="btn btn-systex">
                        <i class="fa fa-list me-1"></i> Lista completa de registros
                    </a>

                    <a href="{{ route('usuarios.index') }}" class="btn btn-outline-light">
                        <i class="fa fa-users me-1"></i> Gerenciar usuários
                    </a>

                    
                </div>

                <div class="mt-3">
                    <div class="muted small">Dica</div>
                    <div class="small" style="color: rgba(255,255,255,.82);">
                        Use “Relatórios” para exportar e analisar por período.
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ================= ÚLTIMAS BATIDAS (tabela) ================= --}}
    <div class="dash-card p-3">
        <div class="d-flex align-items-center justify-content-between mb-2">
            <div>
                <div class="fw-bold">Últimas batidas</div>
                <div class="muted small">Últimos registros cadastrados no sistema</div>
            </div>
            <a href="" class="btn btn-outline-light btn-sm">
                Ver tudo <i class="fa fa-arrow-right ms-1"></i>
            </a>
        </div>

        @if(!empty($ultimosRegistros ?? null) && count($ultimosRegistros))
            <div class="table-responsive dash-table">
                <table class="table table-dark table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Colaborador</th>
                            <th>CPF</th>
                            <th>Tipo</th>
                            <th>Data/Hora</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($ultimosRegistros as $r)
                            @php
                                $tipo = $r->tipo ?? '';
                                $badge = match($tipo){
                                    'entrada' => 'badge-entrada',
                                    'intervalo' => 'badge-intervalo',
                                    'retorno' => 'badge-retorno',
                                    default => 'badge-saida'
                                };
                            @endphp
                            <tr>
                                <td class="fw-semibold">{{ $r->user->name ?? '-' }}</td>
                                <td class="text-muted">{{ $r->user->cpf ?? '-' }}</td>
                                <td><span class="badge-soft {{ $badge }}">{{ ucfirst($tipo) }}</span></td>
                                <td class="text-muted">
                                    {{ \Carbon\Carbon::parse($r->created_at)->format('d/m/Y H:i') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="empty-state">
                <div class="fw-bold mb-1">Nada para mostrar ainda</div>
                <div class="small">
                    Assim que você passar <code>$ultimosRegistros</code> pelo controller (ex.: últimos 10),
                    essa tabela começa a preencher a tela e o dashboard fica “vivo”.
                </div>
            </div>
        @endif
    </div>
@endsection

@push('scripts')
    @if(!empty($chartLabels ?? null) && !empty($chartValues ?? null))
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
        <script>
            (function(){
                const el = document.getElementById('chartRegistros');
                if(!el) return;

                const labels = @json($chartLabels);
                const values = @json($chartValues);

                new Chart(el, {
                    type: 'line',
                    data: {
                        labels,
                        datasets: [{
                            label: 'Registros',
                            data: values,
                            tension: 0.35,
                            fill: true,
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: { display: false },
                            tooltip: { intersect: false, mode: 'index' }
                        },
                        scales: {
                            x: { ticks: { color: 'rgba(255,255,255,.65)' }, grid: { color: 'rgba(255,255,255,.06)' } },
                            y: { ticks: { color: 'rgba(255,255,255,.65)' }, grid: { color: 'rgba(255,255,255,.06)' } }
                        }
                    }
                });
            })();
        </script>
    @endif
@endpush
