<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <title>{{ $titulo }}</title>
    <style>
        @page { margin: 22mm 8mm 17mm; }
        * { box-sizing: border-box; }
        body { margin: 0; color: #222; font-family: DejaVu Sans, sans-serif; font-size: 8px; line-height: 1.32; }
        .header { position: fixed; top: -17mm; left: 0; right: 0; height: 15mm; border-bottom: 1px solid #c9c9c9; }
        .header-table { width: 100%; border-collapse: collapse; }
        .header-table td { border: 0; padding: 0; vertical-align: middle; }
        .logo { width: 54px; max-height: 35px; object-fit: contain; }
        .brand { color: #a41118; font-size: 15px; font-weight: bold; }
        .title { margin: 0; color: #202020; font-size: 14px; font-weight: bold; text-align: center; }
        .header-meta { color: #555; font-size: 7px; text-align: right; }
        .footer { position: fixed; bottom: -12mm; left: 0; right: 0; padding-top: 4px; border-top: 1px solid #ccc; color: #666; font-size: 6.5px; }
        h2 { margin: 12px 0 6px; padding-bottom: 3px; border-bottom: 1px solid #bbb; color: #8e1118; font-size: 11px; }
        h3 { margin: 0 0 5px; color: #333; font-size: 9px; }
        .meta { width: 100%; margin-bottom: 7px; border-collapse: collapse; }
        .meta td { padding: 5px 6px; border: 1px solid #d5d5d5; background: #f7f7f7; }
        .meta-label { display: block; color: #707070; font-size: 6px; text-transform: uppercase; }
        .meta-value { display: block; margin-top: 2px; font-size: 8px; font-weight: bold; }
        .summary { width: 100%; margin-bottom: 8px; border-collapse: separate; border-spacing: 3px; }
        .summary td { width: 20%; padding: 6px; border: 1px solid #d3d3d3; border-left: 3px solid #a41118; background: #fafafa; vertical-align: top; }
        .summary strong { display: block; font-size: 12px; }
        .summary span { color: #666; font-size: 6.5px; }
        .columns { width: 100%; border-collapse: separate; border-spacing: 5px 0; }
        .columns > tbody > tr > td { vertical-align: top; }
        .data-table { width: 100%; border-collapse: collapse; }
        .data-table th { padding: 4px; border: 1px solid #aaa; background: #343434; color: #fff; font-size: 6px; text-align: left; text-transform: uppercase; }
        .data-table td { padding: 3px 4px; border: 1px solid #d2d2d2; vertical-align: top; }
        .data-table tbody tr:nth-child(even) td { background: #f5f5f5; }
        .data-table .number { text-align: right; }
        .compact th, .compact td { padding: 3px; font-size: 6.5px; }
        .detail { font-size: 5.8px; }
        .detail th, .detail td { padding: 2.5px 2px; }
        .badge { display: inline-block; padding: 1px 3px; border: 1px solid #999; border-radius: 2px; background: #f2f2f2; font-size: 5.5px; white-space: nowrap; }
        .badge-extra, .badge-delay { border-color: #b47600; background: #fff4d6; }
        .badge-ok { border-color: #54865f; background: #eaf6ed; }
        .badge-cancel { border-color: #888; background: #eee; }
        .muted { color: #777; }
        .page-break { page-break-before: always; }
        tr, .avoid-break { page-break-inside: avoid; }
        .declaration { margin-top: 18px; padding: 12px; border: 1px solid #aaa; }
        .declaration p { margin: 0 0 22px; font-size: 8px; }
        .signature { width: 100%; border-collapse: separate; border-spacing: 12px 0; }
        .signature td { width: 33%; padding-top: 18px; border-top: 1px solid #555; text-align: center; }
        .filters { margin: 5px 0 8px; padding: 5px 7px; border: 1px solid #ddd; background: #fafafa; color: #555; }
    </style>
</head>
<body>
    <header class="header">
        <table class="header-table"><tr>
            <td style="width:22%">
                @if($logoDataUri)<img src="{{ $logoDataUri }}" class="logo" alt="MaxTur">@else<span class="brand">MaxTur</span>@endif
            </td>
            <td style="width:56%"><div class="title">{{ $titulo }}</div></td>
            <td style="width:22%" class="header-meta">Gerado em<br><strong>{{ $geradoEm->format('d/m/Y H:i') }}</strong></td>
        </tr></table>
    </header>

    <footer class="footer">Relatório gerado automaticamente pelo sistema MaxTur · {{ $geradoEm->format('d/m/Y H:i') }}</footer>

    <table class="meta"><tr>
        <td><span class="meta-label">Cliente</span><span class="meta-value">{{ $clienteLabel }}</span></td>
        <td><span class="meta-label">Período analisado</span><span class="meta-value">{{ $periodoLabel }}</span></td>
        <td><span class="meta-label">Data da geração</span><span class="meta-value">{{ $geradoEm->format('d/m/Y H:i:s') }}</span></td>
        <td><span class="meta-label">Finalidade</span><span class="meta-value">Conferência e pré-faturamento</span></td>
    </tr></table>
    <div class="filters"><strong>Filtros aplicados:</strong> {{ implode(' · ', $filtros) }}</div>

    <h2>1. Resumo executivo</h2>
    <table class="summary"><tr>
        <td><strong>{{ $totais['total'] }}</strong><span>Total geral de viagens</span></td>
        <td><strong>{{ $totais['programadas'] }}</strong><span>Viagens programadas</span></td>
        <td><strong>{{ $totais['extras'] }}</strong><span>Viagens extras</span></td>
        <td><strong>{{ $totalizadores['tipo']['Diárias'] }}</strong><span>Viagens diárias</span></td>
        <td><strong>{{ $totalizadores['tipo']['Mensais'] }}</strong><span>Viagens mensais</span></td>
    </tr><tr>
        <td><strong>{{ $totalizadores['tipo']['Esporádicas'] }}</strong><span>Viagens esporádicas</span></td>
        <td><strong>{{ $totais['finalizadas'] }}</strong><span>Viagens finalizadas</span></td>
        <td><strong>{{ $totais['canceladas'] }}</strong><span>Viagens canceladas</span></td>
        <td><strong>{{ $totais['atrasadas'] }}</strong><span>Viagens com atraso</span></td>
        <td><strong>{{ number_format($totais['minutos_atraso'], 0, ',', '.') }}</strong><span>Minutos totais de atraso</span></td>
    </tr><tr>
        <td><strong>{{ number_format($totais['media_atraso'], 1, ',', '.') }} min</strong><span>Média por viagem com atraso</span></td>
        <td><strong>{{ $totais['ocorrencias'] }}</strong><span>Ocorrências</span></td>
        <td><strong>{{ $totais['veiculos'] }}</strong><span>Veículos utilizados</span></td>
        <td><strong>{{ $totais['motoristas'] }}</strong><span>Motoristas utilizados</span></td>
        <td><strong>{{ $totais['atrasos_registrados'] }}</strong><span>Atrasos registrados</span></td>
    </tr></table>

    <h2>2. Totalizadores</h2>
    <table class="columns"><tr>
        <td style="width:25%"><h3>Por natureza</h3><table class="data-table compact"><thead><tr><th>Natureza</th><th>Viagens</th></tr></thead><tbody>@foreach($totalizadores['natureza'] as $nome => $total)<tr><td>{{ $nome }}</td><td class="number">{{ $total }}</td></tr>@endforeach</tbody></table></td>
        <td style="width:25%"><h3>Por tipo/período</h3><table class="data-table compact"><thead><tr><th>Tipo</th><th>Viagens</th></tr></thead><tbody>@foreach($totalizadores['tipo'] as $nome => $total)<tr><td>{{ $nome }}</td><td class="number">{{ $total }}</td></tr>@endforeach</tbody></table></td>
        <td style="width:25%"><h3>Por status</h3><table class="data-table compact"><thead><tr><th>Status</th><th>Viagens</th></tr></thead><tbody>@forelse($totalizadores['status'] as $nome => $total)<tr><td>{{ $nome }}</td><td class="number">{{ $total }}</td></tr>@empty<tr><td colspan="2">Sem dados</td></tr>@endforelse</tbody></table></td>
        <td style="width:25%"><h3>Por cliente</h3><table class="data-table compact"><thead><tr><th>Cliente</th><th>Viagens</th></tr></thead><tbody>@forelse($totalizadores['clientes'] as $nome => $total)<tr><td>{{ $nome }}</td><td class="number">{{ $total }}</td></tr>@empty<tr><td colspan="2">Sem dados</td></tr>@endforelse</tbody></table></td>
    </tr></table>

    <table class="columns" style="margin-top:9px"><tr>
        <td style="width:34%"><h3>Por motorista</h3><table class="data-table compact"><thead><tr><th>Motorista</th><th>Viagens</th><th>Atrasos</th><th>Minutos</th></tr></thead><tbody>@forelse($totalizadores['motoristas'] as $item)<tr><td>{{ $item['nome'] }}</td><td class="number">{{ $item['viagens'] }}</td><td class="number">{{ $item['atrasos'] }}</td><td class="number">{{ $item['minutos'] }}</td></tr>@empty<tr><td colspan="4">Sem dados</td></tr>@endforelse</tbody></table></td>
        <td style="width:33%"><h3>Por veículo</h3><table class="data-table compact"><thead><tr><th>Veículo/placa</th><th>Viagens</th><th>Atrasos</th><th>Minutos</th></tr></thead><tbody>@forelse($totalizadores['veiculos'] as $item)<tr><td>{{ $item['nome'] }}</td><td class="number">{{ $item['viagens'] }}</td><td class="number">{{ $item['atrasos'] }}</td><td class="number">{{ $item['minutos'] }}</td></tr>@empty<tr><td colspan="4">Sem dados</td></tr>@endforelse</tbody></table></td>
        <td style="width:33%"><h3>Por trajeto</h3><table class="data-table compact"><thead><tr><th>Origem → Destino</th><th>Viagens</th></tr></thead><tbody>@forelse($totalizadores['trajetos'] as $nome => $total)<tr><td>{{ $nome }}</td><td class="number">{{ $total }}</td></tr>@empty<tr><td colspan="2">Sem dados</td></tr>@endforelse</tbody></table></td>
    </tr></table>

    <div class="page-break"></div>
    <h2>3. Detalhamento das viagens</h2>
    <table class="data-table detail">
        <thead><tr><th>Nº</th><th>Data</th><th>Hora</th><th>Cliente</th><th>Origem</th><th>Destino</th><th>Motorista</th><th>Veículo</th><th>Natureza</th><th>Tipo</th><th>Status</th><th>Checklist</th><th>Atraso</th><th>Ocorr.</th><th>Observações</th></tr></thead>
        <tbody>
        @forelse($viagens as $viagem)
            @php($atrasoTotal = (int) $viagem->atraso_viagem_total + (int) $viagem->atraso_passageiro_total)
            <tr>
                <td>#{{ $viagem->id }}</td><td>{{ $viagem->data_hora?->format('d/m/Y') }}</td><td>{{ $viagem->data_hora?->format('H:i') }}</td>
                <td>{{ $viagem->cliente?->nome_fantasia ?: $viagem->cliente?->razao_social ?: 'Não informado' }}</td><td>{{ $viagem->origem }}</td><td>{{ $viagem->destino }}</td>
                <td>{{ $viagem->ultimaAtribuicao?->motorista?->name ?: 'Não informado' }}</td><td>{{ $viagem->ultimaAtribuicao?->veiculo?->placa ?: 'Não informado' }}</td>
                <td><span class="badge {{ $viagem->natureza === 'extra' ? 'badge-extra' : '' }}">{{ $viagem->natureza === 'extra' ? 'Extra' : ($viagem->natureza === 'programada' ? 'Programada' : 'Não informado') }}</span></td>
                <td>{{ ['diario'=>'Diário','mensal'=>'Mensal','esporadico'=>'Esporádico'][$viagem->tipo_periodo] ?? 'Não informado' }}</td><td>{{ $viagem->statusLabel() }}</td>
                <td>{{ $viagem->ultimoChecklist?->status ? ucfirst(str_replace('_', ' ', $viagem->ultimoChecklist->status)) : 'Não iniciado' }}</td>
                <td><span class="badge {{ $atrasoTotal ? 'badge-delay' : 'badge-ok' }}">{{ $atrasoTotal }} min</span></td><td>{{ $viagem->ocorrencias_count }}</td><td>{{ $viagem->observacao ?: '—' }}</td>
            </tr>
        @empty<tr><td colspan="15">Nenhuma viagem encontrada para os filtros informados.</td></tr>@endforelse
        </tbody>
    </table>

    @if($eventos->count())
        <div class="page-break"></div>
        <h2>4. Atrasos e ocorrências</h2>
        <table class="data-table compact">
            <thead><tr><th>Viagem</th><th>Data/hora real</th><th>Tipo</th><th>Motorista</th><th>Passageiro</th><th>Minutos</th><th>Motivo/descrição</th><th>Registrado em</th></tr></thead>
            <tbody>@foreach($eventos as $evento)<tr><td>#{{ $evento['viagem_id'] }}</td><td>{{ $evento['ocorrido_em']?->format('d/m/Y H:i') }}</td><td>{{ $evento['tipo'] }}</td><td>{{ $evento['motorista'] }}</td><td>{{ $evento['passageiro'] ?: '—' }}</td><td>{{ $evento['minutos'] ?? '—' }}</td><td>{{ $evento['descricao'] }}</td><td>{{ $evento['registrado_em']?->format('d/m/Y H:i') }}</td></tr>@endforeach</tbody>
        </table>
    @endif

    <div class="declaration avoid-break">
        <h2 style="margin-top:0">5. Validação do cliente</h2>
        <p>Declaro que as viagens relacionadas neste relatório foram conferidas e validadas para fins de fechamento operacional.</p>
        <table class="signature"><tr><td>Nome do responsável</td><td>Cargo</td><td>Data</td></tr></table>
        <table class="signature" style="margin-top:28px"><tr><td colspan="3">Assinatura</td></tr></table>
    </div>

    <script type="text/php">
        if (isset($pdf)) {
            $font = $fontMetrics->get_font('DejaVu Sans', 'normal');
            $pdf->page_text(738, 574, 'Página {PAGE_NUM} de {PAGE_COUNT}', $font, 6.5, array(0.35, 0.35, 0.35));
        }
    </script>
</body>
</html>
