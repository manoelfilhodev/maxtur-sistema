<form method="POST" action="{{ route('password.update') }}">
    @csrf
    @method('PUT')

    <div class="form-group">
        <label>Senha Atual</label>
        <input type="password" name="current_password" class="form-control" required>
    </div>

    <div class="form-group mt-3">
        <label>Nova Senha</label>
        <input type="password" name="password" class="form-control" required>
    </div>

    <div class="form-group mt-3">
        <label>Confirmar Nova Senha</label>
        <input type="password" name="password_confirmation" class="form-control" required>
    </div>

    <button class="btn btn-primary mt-4">
        Alterar Senha
    </button>
</form>
