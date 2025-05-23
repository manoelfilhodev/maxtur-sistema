<form id="form-excluir" method="POST" action="{{ route('profile.destroy') }}">
    @csrf
    @method('DELETE')

    <div class="alert alert-danger">
        <p><strong>Atenção:</strong> Esta ação é irreversível. Sua conta será excluída permanentemente.</p>
    </div>

    <a href="#" onclick="confirmarExclusao(event)" class="btn btn-outline-danger mt-4">
        <i class="bi bi-trash"></i> Excluir Conta
    </a>
</form>