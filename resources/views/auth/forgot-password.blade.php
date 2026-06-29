@extends('layouts.auth')

@section('content')

<div class="text-center mb-4">
    <img src="{{ asset('images/logo-sem-nome.png') }}" alt="Logotipo MaxTur" class="mb-3" height="120">
    <h4 class="fw-bold">Recuperar senha</h4>
    <p class="small auth-copy">
        Informe seu email e cadastre uma nova senha.
    </p>
</div>

@if (session('success'))
    <div class="alert alert-success d-flex align-items-center shadow-sm" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i>
        <span>{{ session('success') }}</span>
    </div>
@endif

@if ($errors->any())
    <div class="alert alert-danger d-flex align-items-start shadow-sm" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2 mt-1"></i>
        <div>
            @foreach ($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    </div>
@endif

<form method="POST" action="{{ route('password.update') }}">
    @csrf

    <div class="mb-3">
        <label class="form-label text-white" for="recovery-email">E-mail</label>
        <input id="recovery-email" type="email" name="email" value="{{ old('email') }}"
               class="form-control @error('email') is-invalid @enderror" placeholder="Digite seu email" required autofocus>
        @error('email')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-3">
        <label class="form-label text-white" for="recovery-password">Nova senha</label>
        <input id="recovery-password" type="password" name="password" autocomplete="new-password"
               class="form-control @error('password') is-invalid @enderror" placeholder="Mínimo de 8 caracteres" required>
        @error('password')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-3">
        <label class="form-label text-white" for="recovery-confirmation">Confirmar senha</label>
        <input id="recovery-confirmation" type="password" name="password_confirmation" autocomplete="new-password"
               class="form-control" placeholder="Digite a senha novamente" required>
    </div>

    <button class="btn btn-login w-100 mt-3"><b>Salvar nova senha</b></button>

    <div class="mt-4 text-center">
        <a href="{{ route('login') }}" class="text-white-50 text-decoration-none">Voltar para o login</a>
    </div>

    <footer class="mt-4 text-center text-white-50">
        © {{ date('Y') }} Systex Sistemas Inteligentes
    </footer>
</form>

@endsection
