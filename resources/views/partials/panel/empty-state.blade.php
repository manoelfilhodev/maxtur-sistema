<div class="sx-empty">
    <div class="sx-empty-icon">
        <i class="{{ $icon ?? 'bi bi-inbox' }}"></i>
    </div>
    <div class="sx-empty-title">{{ $title }}</div>
    <div class="sx-empty-text">{{ $message }}</div>
    @isset($actionRoute)
        <a href="{{ $actionRoute }}" class="btn btn-systex btn-sm mt-1">
            <i class="{{ $actionIcon ?? 'bi bi-plus-circle' }}"></i> {{ $actionLabel }}
        </a>
    @endisset
</div>
