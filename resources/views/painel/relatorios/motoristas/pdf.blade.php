<!doctype html>
<html lang="pt-BR"><head><meta charset="utf-8"><title>Validação do motorista</title>
<style>
    @page { margin: 24px 28px 30px; } * { box-sizing: border-box; } body { font: 10px DejaVu Sans, sans-serif; color: #172033; margin: 0; }
    .header { width: 100%; border-collapse: collapse; border-bottom: 3px solid #155eef; margin-bottom: 12px; } .header td { border: 0; padding: 0 0 10px; vertical-align: middle; } .header .logo-cell { width: 120px; padding-right: 15px; } .logo { display: block; width: 105px; max-height: 48px; object-fit: contain; } h1 { font-size: 19px; margin: 0 0 3px; } .muted { color: #667085; }
    .driver { background: #eef4ff; border: 1px solid #c7d7fe; padding: 9px; margin-bottom: 10px; } .driver strong { font-size: 13px; }
    .kpis { width: 100%; border-collapse: separate; border-spacing: 5px; margin: 0 -5px 10px; } .kpis td { border: 1px solid #d0d5dd; padding: 7px; width: 14.28%; } .kpis b { display: block; font-size: 14px; margin-top: 3px; }
    table.data { width: 100%; border-collapse: collapse; } .data th { background: #172033; color: white; font-size: 8px; padding: 5px 4px; text-align: left; } .data td { border-bottom: 1px solid #e4e7ec; padding: 5px 4px; vertical-align: top; } .right { text-align: right; } .nowrap { white-space: nowrap; }
    .note { margin-top: 10px; padding: 7px; border: 1px solid #f4c790; background: #fff8eb; font-size: 9px; }
    .signatures { margin-top: 42px; width: 100%; } .signature { width: 43%; display: inline-block; text-align: center; border-top: 1px solid #344054; padding-top: 5px; } .signature + .signature { margin-left: 12%; }
    .footer { position: fixed; bottom: -18px; left: 0; right: 0; color: #667085; font-size: 8px; border-top: 1px solid #e4e7ec; padding-top: 4px; } .page:after { content: counter(page); }
</style></head><body>
<table class="header"><tr>
    @if($logoDataUri)<td class="logo-cell"><img class="logo" src="{{ $logoDataUri }}"></td>@endif
    <td><h1>Relatório de validação do motorista</h1><div class="muted">Período: {{ $periodoLabel }} · gerado em {{ $geradoEm->format('d/m/Y H:i') }}</div></td>
</tr></table>

<div class="driver">
@if($motorista)
    <strong>{{ $motorista->name }}</strong><br>CPF: {{ $motorista->cpf ?: 'Não informado' }} · Regra: {{ $motorista->tipo_recebimento === 'por_viagem' ? 'R$ '.number_format((float) $motorista->valor_por_viagem, 2, ',', '.').' por viagem finalizada' : 'Salário contratual de R$ '.number_format((float) $motorista->valor_salario, 2, ',', '.') }}
@else
    <strong>Todos os motoristas</strong><br>Relatório consolidado conforme os filtros selecionados.
@endif
</div>

<table class="kpis"><tr><td>Viagens<b>{{ $totais['viagens'] }}</b></td><td>Finalizadas<b>{{ $totais['finalizadas'] }}</b></td><td>Extras<b>{{ $totais['extras'] }}</b></td><td>Canceladas<b>{{ $totais['canceladas'] }}</b></td><td>Atraso total<b>{{ $totais['minutos_atraso'] }} min</b></td><td>Ocorrências<b>{{ $totais['ocorrencias'] }}</b></td><td>Calculado<b>R$ {{ number_format($totais['valor_calculado'], 2, ',', '.') }}</b></td></tr></table>

<table class="data"><thead><tr><th>ID</th><th>Data/hora</th>@unless($motorista)<th>Motorista</th>@endunless<th>Cliente</th><th>Origem → destino</th><th>Veículo</th><th>Natureza</th><th>Status</th><th class="right">Atraso</th><th class="right">Ocorr.</th><th class="right">Valor</th></tr></thead><tbody>
@forelse($viagens as $viagem)
    @php $condutor = $viagem->ultimaAtribuicao?->motorista; $atraso = (int) $viagem->atraso_viagem_total + (int) $viagem->atraso_passageiro_total; $elegivel = $viagem->status === \App\Support\ViagemStatus::FINALIZADA && $condutor?->tipo_recebimento === 'por_viagem'; @endphp
    <tr><td>#{{ $viagem->id }}</td><td class="nowrap">{{ $viagem->data_hora?->format('d/m/Y H:i') }}</td>@unless($motorista)<td>{{ $condutor?->name ?: 'Não informado' }}</td>@endunless<td>{{ $viagem->cliente?->nome_fantasia ?: $viagem->cliente?->razao_social ?: 'Não informado' }}</td><td>{{ $viagem->origem }} → {{ $viagem->destino }}</td><td>{{ $viagem->ultimaAtribuicao?->veiculo?->placa ?: '—' }}</td><td>{{ $viagem->natureza === 'extra' ? 'Extra' : 'Programada' }}</td><td>{{ $viagem->statusLabel() }}</td><td class="right">{{ $atraso }} min</td><td class="right">{{ $viagem->ocorrencias_count }}</td><td class="right">{{ $elegivel ? 'R$ '.number_format((float) $condutor->valor_por_viagem, 2, ',', '.') : '—' }}</td></tr>
@empty<tr><td colspan="{{ $motorista ? 10 : 11 }}">Nenhuma viagem encontrada no período.</td></tr>@endforelse
</tbody></table>

<div class="note">Este documento confere os lançamentos operacionais. Para remuneração por viagem, somente registros com status “Finalizada” compõem o valor calculado. Salário, descontos, adicionais, benefícios e ajustes manuais devem ser validados pela regra da folha.</div>
<div class="signatures">@if($motorista)<div class="signature">{{ $motorista->name }}<br><span class="muted">Motorista</span></div><div class="signature">Responsável pela conferência<br><span class="muted">MaxTur</span></div>@else<div class="signature">Responsável pela conferência<br><span class="muted">MaxTur</span></div>@endif</div>
<div class="footer">MaxTur · relatório de validação do motorista <span style="float:right">Página <span class="page"></span></span></div>
</body></html>
