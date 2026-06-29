@extends('layouts.app')

@section('page-heading')
    @include('partials.panel.page-header', [
        'title' => 'Checklist #'.$checklist->id,
        'subtitle' => 'Resultado detalhado da inspeção operacional',
        'backRoute' => route('painel.operador.checklists.index'),
    ])
@endsection

@section('content')
    <div class="sx-container">
    <div class="sx-card mb-3">
        <div class="card-body">
            <div><strong>Veiculo:</strong> {{ $checklist->veiculo->placa ?? '-' }} {{ $checklist->veiculo->modelo ?? '' }}</div>
            <div><strong>Motorista:</strong> {{ $checklist->motorista->name ?? '-' }}</div>
            <div><strong>Status:</strong> @include('partials.panel.status-badge', ['status' => $checklist->status])</div>
            <div><strong>Resultado:</strong> @include('partials.panel.status-badge', ['status' => $checklist->resultado ?? 'pendente'])</div>
        </div>
    </div>

    <div class="sx-card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-systex-grid">
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
                                <td>@include('partials.panel.status-badge', ['status' => $resposta->status])</td>
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
    </div>
@endsection
