@extends('layouts.app')

@section('page-heading')
    <h3 class="text-white mb-0">Operador - Checklists</h3>
@endsection

@section('content')
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-dark table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Veiculo</th>
                            <th>Motorista</th>
                            <th>Status</th>
                            <th>Resultado</th>
                            <th>Inicio</th>
                            <th>Fim</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($checklists as $checklist)
                            <tr>
                                <td>{{ $checklist->id }}</td>
                                <td>{{ $checklist->veiculo->placa ?? '-' }}</td>
                                <td>{{ $checklist->motorista->name ?? '-' }}</td>
                                <td>{{ $checklist->status }}</td>
                                <td>{{ $checklist->resultado ?? '-' }}</td>
                                <td>{{ optional($checklist->started_at)->format('d/m/Y H:i') }}</td>
                                <td>{{ optional($checklist->finished_at)->format('d/m/Y H:i') }}</td>
                                <td>
                                    <a class="btn btn-sm btn-outline-light" href="{{ route('painel.operador.checklists.show', $checklist->id) }}">Detalhes</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8">Nenhum checklist encontrado.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $checklists->links() }}
        </div>
    </div>
@endsection

