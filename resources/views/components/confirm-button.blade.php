@props([
    'form' => 'form-id',
    'message' => 'Deseja realmente executar esta ação?',
    'title' => 'Tem certeza?',
    'icon' => 'warning',
    'confirmText' => 'Sim, confirmar',
    'cancelText' => 'Cancelar',
    'buttonClass' => 'btn btn-danger',
    'buttonLabel' => 'Excluir',
    'iconButton' => 'bi bi-trash'
])

<form id="{{ $form }}" method="POST" action="{{ $attributes['action'] }}">
    @csrf
    @method($attributes['method'] ?? 'POST')
</form>

<button class="{{ $buttonClass }}" onclick="confirmarAcao{{ $form }}()">
    <i class="{{ $iconButton }}"></i> {{ $buttonLabel }}
</button>

<script>
    function confirmarAcao{{ $form }}() {
        Swal.fire({
            title: '{{ $title }}',
            text: '{{ $message }}',
            icon: '{{ $icon }}',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '{{ $confirmText }}',
            cancelButtonText: '{{ $cancelText }}'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('{{ $form }}').submit();
            }
        });
    }
</script>
