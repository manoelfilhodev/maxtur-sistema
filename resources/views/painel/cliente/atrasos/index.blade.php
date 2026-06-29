@extends('layouts.app')

@section('page-heading')
    <h3 class="text-white mb-0">Cliente - Relatorio de Atrasos</h3>
@endsection

@section('content')
    <div class="card mb-3">
        <div class="card-body">
            <h5>Atrasos de viagem</h5>
            <div class="table-responsive">
                <table class="table table-dark table-striped">
                    <thead><tr><th>Solicitação</th><th>Minutos</th><th>Motivo</th><th>Ocorrido em</th><th>Registrado em</th></tr></thead>
                    <tbody>
                        @forelse($atrasosViagem as $atraso)
                            <tr>
                                <td>#{{ $atraso->solicitacao_id }}</td>
                                <td>{{ $atraso->minutos_atraso }}</td>
                                <td>{{ $atraso->motivo ?? '-' }}</td>
                                <td>{{ ($atraso->ocorrido_em ?? $atraso->created_at)->format('d/m/Y H:i') }}</td>
                                <td>{{ $atraso->created_at->format('d/m/Y H:i') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5">Sem atrasos de viagem.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $atrasosViagem->links() }}
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <h5>Atrasos por passageiro</h5>
            <div class="table-responsive">
                <table class="table table-dark table-striped">
                    <thead><tr><th>Solicitação</th><th>Passageiro</th><th>Minutos</th><th>Motivo</th><th>Ocorrido em</th><th>Registrado em</th></tr></thead>
                    <tbody>
                        @forelse($atrasosPassageiro as $atraso)
                            <tr>
                                <td>#{{ $atraso->solicitacao_id }}</td>
                                <td>{{ $atraso->passageiro->nome ?? '-' }}</td>
                                <td>{{ $atraso->minutos_atraso }}</td>
                                <td>{{ $atraso->motivo ?? '-' }}</td>
                                <td>{{ ($atraso->ocorrido_em ?? $atraso->created_at)->format('d/m/Y H:i') }}</td>
                                <td>{{ $atraso->created_at->format('d/m/Y H:i') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6">Sem atrasos por passageiro.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $atrasosPassageiro->links() }}
        </div>
    </div>
@endsection
