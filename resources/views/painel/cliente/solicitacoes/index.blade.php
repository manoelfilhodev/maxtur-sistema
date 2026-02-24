@extends('layouts.app')

@section('page-heading')
    <h3 class="text-white mb-0">Cliente - Minhas Solicitacoes</h3>
@endsection

@section('content')
    <div class="card">
        <div class="card-body">
            <form method="GET" class="row g-2 mb-3">
                <div class="col-md-3">
                    <select name="status" class="form-select">
                        <option value="">Todos os status</option>
                        @foreach(['aberta','em_analise','aprovada','programada','realizada','cancelada','rejeitada'] as $status)
                            <option value="{{ $status }}" @selected(request('status') === $status)>{{ $status }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <input type="date" name="data_inicio" class="form-control" value="{{ request('data_inicio') }}">
                </div>
                <div class="col-md-3">
                    <input type="date" name="data_fim" class="form-control" value="{{ request('data_fim') }}">
                </div>
                <div class="col-md-2">
                    <button class="btn btn-systex w-100">Filtrar</button>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-dark table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Origem</th>
                            <th>Destino</th>
                            <th>Data/Hora</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($solicitacoes as $solicitacao)
                            <tr>
                                <td>{{ $solicitacao->id }}</td>
                                <td>{{ $solicitacao->origem }}</td>
                                <td>{{ $solicitacao->destino }}</td>
                                <td>{{ optional($solicitacao->data_hora)->format('d/m/Y H:i') }}</td>
                                <td>{{ $solicitacao->status }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5">Sem solicitacoes.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $solicitacoes->links() }}
        </div>
    </div>
@endsection

