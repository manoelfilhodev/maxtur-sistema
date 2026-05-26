<div class="sx-page-header">
    <div>
        <h3 class="sx-page-title">{{ $title }}</h3>
        @isset($subtitle)
            <div class="sx-page-subtitle">{{ $subtitle }}</div>
        @endisset
    </div>

    @isset($actionRoute)
        <div class="sx-page-actions">
            <a href="{{ $actionRoute }}" class="btn btn-systex btn-sm">
                <i class="{{ $actionIcon ?? 'bi bi-plus-circle' }}"></i> {{ $actionLabel }}
            </a>
        </div>
    @endisset
</div>
