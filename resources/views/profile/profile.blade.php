@extends('layouts.app')

@section('title', 'Meu Perfil')

@section('content')
<div class="page-heading">
    <h3>Meu Perfil</h3>
</div>

<div class="page-content">
    <section class="row">
        <div class="col-12 col-md-8 offset-md-2">
            <div class="card">
                <div class="card-header">
                    <h4>Informações do Perfil</h4>
                </div>
                <div class="card-body">

                    {{-- Atualizar informações do usuário --}}
                    @include('profile.partials.update-profile-information-form')

                    <hr class="my-4">

                    {{-- Atualizar senha --}}
                    @include('profile.partials.update-password-form')

                    <hr class="my-4">

                    {{-- Deletar conta --}}
                    @include('profile.partials.delete-user-form')

                </div>
            </div>
        </div>
    </section>
</div>
@endsection
