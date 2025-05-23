<form method="POST" action="{{ route('profile.update') }}">
    @csrf
    @method('PATCH')

    <div class="form-group">
        <label>Nome</label>
        <input type="text" class="form-control" name="name" value="{{ old('name', auth()->user()->name) }}" required>
    </div>

    <div class="form-group mt-3">
        <label>Email</label>
        <input type="email" class="form-control" name="email" value="{{ old('email', auth()->user()->email) }}" required>
    </div>

    <button class="btn btn-primary mt-4">
        Salvar Alterações
    </button>
</form>
