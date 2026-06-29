@extends('layouts.app')

@section('page-heading')
    <div class="sx-page-header">
        <div>
            <h3 class="sx-page-title">Novo veículo</h3>
            <div class="sx-page-subtitle">Cadastre um veículo disponível para programação de viagens</div>
        </div>
        <div class="sx-page-actions">
            <a class="btn btn-outline-light btn-sm" href="{{ route('master.veiculos.index') }}">
                <i class="bi bi-arrow-left"></i> Voltar
            </a>
        </div>
    </div>
@endsection

@section('content')
<div class="sx-container">
    @include('master.veiculos._form', [
        'action' => route('master.veiculos.store'),
        'cancelRoute' => route('master.veiculos.index'),
        'submitLabel' => 'Salvar veículo',
    ])
</div>
@endsection
