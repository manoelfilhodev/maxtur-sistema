@foreach(['success' => 'success', 'error' => 'danger', 'warning' => 'warning', 'info' => 'info'] as $key => $type)
    @if(session($key))
        <div class="alert alert-{{ $type }} alert-dismissible fade show sx-alert" role="status" aria-live="polite">
            <i class="bi {{ $type === 'success' ? 'bi-check-circle-fill' : ($type === 'danger' ? 'bi-exclamation-triangle-fill' : 'bi-info-circle-fill') }}" aria-hidden="true"></i>
            <span>{{ session($key) }}</span>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Fechar mensagem"></button>
        </div>
    @endif
@endforeach
