<div class="sx-page-header">
    <div>
        <h3 class="sx-page-title">{{ $title }}</h3>
        @isset($subtitle)
            <div class="sx-page-subtitle">{{ $subtitle }}</div>
        @endisset
    </div>

    @if(isset($actionRoute) || isset($backRoute))
        <div class="sx-page-actions">
            @isset($backRoute)
                <a href="{{ $backRoute }}" class="btn btn-outline-light btn-sm">
                    <i class="bi bi-arrow-left" aria-hidden="true"></i> {{ $backLabel ?? 'Voltar' }}
                </a>
            @endisset
            @isset($actionRoute)
                <a href="{{ $actionRoute }}" class="btn btn-systex btn-sm">
                    <i class="{{ $actionIcon ?? 'bi bi-plus-circle' }}" aria-hidden="true"></i> {{ $actionLabel }}
                </a>
            @endisset
        </div>
    @endif
</div>
