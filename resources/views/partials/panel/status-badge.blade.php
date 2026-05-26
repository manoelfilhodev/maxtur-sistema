@php
    $value = trim((string) $status);
    $key = \Illuminate\Support\Str::of($value)->lower()->ascii()->replace([' ', '_'], '-')->toString();
    $badgeClass = match ($key) {
        'ativo', 'liberado', 'lido', 'realizada', 'ok' => 'sx-badge-success',
        'inativo', 'finalizada', 'cancelada', 'encerrada' => 'sx-badge-muted',
        'bloqueado', 'atrasada', 'critica', 'falha' => 'sx-badge-danger',
        'pendente', 'programada', 'novo' => 'sx-badge-warning',
        'em-andamento', 'andamento', 'sugestao' => 'sx-badge-info',
        default => 'sx-badge-muted',
    };
@endphp

<span class="sx-badge {{ $badgeClass }}">
    @isset($icon)
        <i class="{{ $icon }}"></i>
    @endisset
    {{ $label ?? \Illuminate\Support\Str::of($value)->replace('_', ' ')->upper() }}
</span>
