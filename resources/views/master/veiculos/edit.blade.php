@extends('layouts.app')

@section('page-heading')
<div class="sx-page-header">
    <div><h3 class="sx-page-title">Editar veículo {{ $veiculo->placa }}</h3><div class="sx-page-subtitle">Atualize cadastro, documento e quilometragem</div></div>
    <a class="btn btn-outline-light btn-sm" href="{{ route('master.veiculos.show', $veiculo) }}"><i class="bi bi-arrow-left"></i> Voltar</a>
</div>
@endsection

@section('content')
<div class="sx-container">
    @include('master.veiculos._form', [
        'action' => route('master.veiculos.update', $veiculo),
        'method' => 'PUT',
        'cancelRoute' => route('master.veiculos.show', $veiculo),
        'submitLabel' => 'Atualizar veículo',
    ])
</div>
@endsection
