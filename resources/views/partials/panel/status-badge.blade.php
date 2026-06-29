@php
    $value = trim((string) $status);
    $key = \Illuminate\Support\Str::of($value)->lower()->ascii()->replace([' ', '_'], '-')->toString();
    $badgeClass = match ($key) {
        'ativo', 'liberado', 'lido', 'realizada', 'finalizada', 'finalizado', 'concluido', 'aprovado', 'apto', 'ok', 'mensal' => 'sx-badge-success',
        'inativo', 'cancelada', 'encerrada' => 'sx-badge-muted',
        'bloqueado', 'atrasada', 'critica', 'falha', 'reprovado', 'nao-conforme', 'vencido' => 'sx-badge-danger',
        'pendente', 'programada', 'checklist-pendente', 'novo', 'extra', 'proximo-vencimento', 'esporadico' => 'sx-badge-warning',
        'solicitada', 'em-analise', 'aprovada', 'pronta-para-execucao', 'em-andamento', 'andamento', 'sugestao', 'diario' => 'sx-badge-info',
        default => 'sx-badge-muted',
    };
    $statusIcon = match ($badgeClass) {
        'sx-badge-success' => 'bi bi-check-circle',
        'sx-badge-danger' => 'bi bi-exclamation-octagon',
        'sx-badge-warning' => 'bi bi-clock-history',
        'sx-badge-info' => 'bi bi-info-circle',
        default => 'bi bi-circle-fill',
    };
@endphp

<span class="sx-badge {{ $badgeClass }}">
    <i class="{{ $icon ?? $statusIcon }}" aria-hidden="true"></i>
    {{ $label ?? \Illuminate\Support\Str::of($value)->replace('_', ' ')->upper() }}
</span>
