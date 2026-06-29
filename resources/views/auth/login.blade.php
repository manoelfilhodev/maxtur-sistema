@extends('layouts.auth')

@section('content')

<div class="text-center mb-4">
    <img src="{{ asset('images/logo-sem-nome.png') }}" alt="Logotipo MaxTur" class="mb-3" height="120">
    <h4 class="fw-bold">{{ config('app.name') }}</h4>
    <p class="small auth-copy">
        Informe suas credenciais para continuar.
    </p>
</div>

@if (session('error'))
    <div class="alert alert-danger d-flex align-items-center shadow-sm" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>
        <span>{{ session('error') }}</span>
    </div>
@endif

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

<form method="POST" action="{{ request()->routeIs('app.login') ? route('app.login.post') : route('login.post') }}">
    @csrf

    <div class="mb-3">
        <label class="form-label text-white" for="login-email">E-mail</label>
        <input id="login-email" type="email" name="email" value="{{ old('email') }}"
               class="form-control @error('email') is-invalid @enderror" placeholder="nome@empresa.com" autocomplete="username" required autofocus>
        @error('email')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-3">
        <div class="d-flex align-items-center justify-content-between gap-3">
            <label class="form-label text-white mb-0" for="login-password">Senha</label>
            <a href="{{ route('password.request') }}" class="small text-white-50 text-decoration-none">Esqueci a senha</a>
        </div>
        <input id="login-password" type="password" name="password"
               class="form-control mt-2 @error('password') is-invalid @enderror" placeholder="Digite sua senha" autocomplete="current-password" required>
        @error('password')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>

    <button class="btn btn-login w-100 mt-3"><i class="bi bi-box-arrow-in-right me-1" aria-hidden="true"></i><b>Entrar</b></button>

    <footer class="mt-4 text-center text-white-50">
        © {{ date('Y') }} Systex Sistemas Inteligentes
    </footer>
</form>

@endsection
