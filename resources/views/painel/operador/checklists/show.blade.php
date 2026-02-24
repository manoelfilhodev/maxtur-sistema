@extends('layouts.app')

@section('page-heading')
    <h3 class="text-white mb-0">Checklist #{{ $checklist->id }}</h3>
@endsection

@section('content')
    <div class="card mb-3">
        <div class="card-body">
            <div><strong>Veiculo:</strong> {{ $checklist->veiculo->placa ?? '-' }} {{ $checklist->veiculo->modelo ?? '' }}</div>
            <div><strong>Motorista:</strong> {{ $checklist->motorista->name ?? '-' }}</div>
            <div><strong>Status:</strong> {{ $checklist->status }}</div>
            <div><strong>Resultado:</strong> {{ $checklist->resultado ?? '-' }}</div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-dark table-striped">
                    <thead>
                        <tr>
                            <th>Codigo</th>
                            <th>Item</th>
                            <th>Status</th>
                            <th>Observacao</th>
                            <th>Foto</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($checklist->respostas as $resposta)
                            <tr>
                                <td>{{ $resposta->codigo ?? ($resposta->item->codigo ?? '-') }}</td>
                                <td>{{ $resposta->item->titulo ?? '-' }}</td>
                                <td>{{ $resposta->status }}</td>
                                <td>{{ $resposta->observacao ?? '-' }}</td>
                                <td>
                                    @if($resposta->foto_path)
                                        <a href="{{ asset($resposta->foto_path) }}" target="_blank">Ver foto</a>
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5">Sem respostas registradas.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

