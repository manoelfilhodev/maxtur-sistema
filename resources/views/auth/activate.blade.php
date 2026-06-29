@extends('layouts.auth')

@section('content')
<div class="text-center mb-4">
    <img src="{{ asset('images/logo-sem-nome.png') }}" alt="Logotipo MaxTur" class="mb-3" height="100">
    <h4 class="fw-bold">Ativação de conta</h4>
    <p class="small auth-copy">
        Defina sua senha para ativar o acesso ao painel.
    </p>
</div>

@if (session('error'))
    <div class="alert alert-danger d-flex align-items-center" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>
        <span>{{ session('error') }}</span>
    </div>
@endif

@if($invalid ?? false)
    <div class="alert alert-danger d-flex align-items-center" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>
        <span>{{ $message ?? 'Link invalido.' }}</span>
    </div>
    <a href="{{ route('login') }}" class="btn btn-login w-100"><b>Voltar para login</b></a>
@else
    @if ($errors->any())
        <div class="alert alert-danger">
            @foreach ($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <div class="mb-3 text-white-50 small">Conta: {{ $email ?? '-' }}</div>

    <form method="POST" action="{{ route('activation.activate', ['token' => $token]) }}">
        @csrf

        <div class="mb-3">
            <label class="form-label text-white" for="activation-password">Nova senha</label>
            <input id="activation-password" type="password" name="password" class="form-control" autocomplete="new-password" required>
        </div>

        <div class="mb-3">
            <label class="form-label text-white" for="activation-confirmation">Confirmar senha</label>
            <input id="activation-confirmation" type="password" name="password_confirmation" class="form-control" autocomplete="new-password" required>
        </div>

        <button class="btn btn-login w-100 mt-2"><b>Ativar conta</b></button>
    </form>
@endif
@endsection
