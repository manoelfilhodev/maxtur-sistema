@extends('layouts.app')

@section('page-heading')
    <div class="sx-page-header">
        <div>
            <h3 class="sx-page-title">Veículo {{ $veiculo->placa }}</h3>
            <div class="sx-page-subtitle">Dados operacionais do veículo</div>
        </div>
        <div class="sx-page-actions">
            <a class="btn btn-outline-light btn-sm" href="{{ route('master.veiculos.index') }}">
                <i class="bi bi-arrow-left"></i> Voltar
            </a>
            <a class="btn btn-systex btn-sm" href="{{ route('master.veiculos.edit', $veiculo) }}"><i class="bi bi-pencil-square"></i> Editar</a>
        </div>
    </div>
@endsection

@section('content')
<div class="sx-container">
    @include('partials.panel.flash-messages')
    @include('partials.panel.form-errors')
    <div class="sx-card">
        <div class="row g-3">
            <div class="col-md-3">
                <div class="sx-kicker">Placa</div>
                <div class="text-white fw-semibold">{{ $veiculo->placa }}</div>
            </div>
            <div class="col-md-4">
                <div class="sx-kicker">Modelo</div>
                <div class="text-white">{{ $veiculo->modelo }}</div>
            </div>
            <div class="col-md-3">
                <div class="sx-kicker">Capacidade</div>
                <div class="text-white">{{ $veiculo->capacidade_passageiros }} passageiros</div>
            </div>
            <div class="col-md-2">
                <div class="sx-kicker">Status</div>
                @include('partials.panel.status-badge', ['status' => $veiculo->status_operacional ?: 'liberado'])
            </div>
            <div class="col-md-2"><div class="sx-kicker">Tipo</div><div class="text-white">{{ $veiculo->tipo === 'parceiro' ? 'Parceiro' : 'Próprio' }}</div></div>
            <div class="col-md-2"><div class="sx-kicker">Ano</div><div class="text-white">{{ $veiculo->ano ?: '-' }}</div></div>
            <div class="col-md-3"><div class="sx-kicker">Documento</div><div class="text-white">{{ $veiculo->data_documento?->format('d/m/Y') ?: '-' }}</div></div>
            <div class="col-md-3"><div class="sx-kicker">Quilometragem</div><div class="text-white">{{ number_format($veiculo->km_atual, 0, ',', '.') }} km</div></div>
        </div>
    </div>

    <div class="sx-card mt-3">
        <div class="sx-card-header">
            <div><h5 class="sx-card-title">Manutenção do veículo</h5><div class="sx-muted small">Alertas consideram 1.000 km ou 30 dias antes do vencimento.</div></div>
        </div>
        <form method="POST" action="{{ route('master.veiculos.manutencoes.store', $veiculo) }}" class="row g-2 align-items-end mb-4">
            @csrf
            <div class="col-md-3"><label class="form-label">Item *</label><input class="form-control" name="item" list="itens-manutencao" value="{{ old('item') }}" required></div>
            <datalist id="itens-manutencao"><option value="Correia dentada"><option value="Troca de óleo"><option value="Filtro de ar"><option value="Filtro de óleo"><option value="Filtro de combustível"></datalist>
            <div class="col-md-2"><label class="form-label">Km referência *</label><input class="form-control" type="number" min="0" name="km_referencia" value="{{ old('km_referencia', $veiculo->km_atual) }}" required></div>
            <div class="col-md-2"><label class="form-label">Km vencimento *</label><input class="form-control" type="number" min="0" name="km_vencimento" value="{{ old('km_vencimento') }}" required></div>
            <div class="col-md-2"><label class="form-label">Data vencimento</label><input class="form-control" type="date" name="data_vencimento" value="{{ old('data_vencimento') }}"></div>
            <div class="col-md-3"><label class="form-label">Observação</label><input class="form-control" name="observacao" value="{{ old('observacao') }}"></div>
            <div class="col-12 text-end"><button class="btn btn-systex btn-sm"><i class="bi bi-plus-circle"></i> Adicionar manutenção</button></div>
        </form>

        @if($veiculo->manutencoes->count())
        <div class="table-responsive sx-table-shell"><table class="table table-hover table-systex-grid"><thead><tr><th>Item</th><th>Referência</th><th>Vencimento</th><th>Status</th><th class="text-end">Ações</th></tr></thead><tbody>
        @foreach($veiculo->manutencoes as $manutencao)
            @php($statusClasses = ['em_dia' => 'success', 'proximo_vencimento' => 'warning', 'vencido' => 'danger'])
            <tr>
                <td>
                    <b>{{ $manutencao->item }}</b>
                    @if($manutencao->observacao)
                        <div class="sx-muted small">{{ $manutencao->observacao }}</div>
                    @endif
                </td>
                <td>{{ number_format($manutencao->km_referencia, 0, ',', '.') }} km</td>
                <td>
                    {{ number_format($manutencao->km_vencimento, 0, ',', '.') }} km
                    @if($manutencao->data_vencimento)
                        <div class="sx-muted small">{{ $manutencao->data_vencimento->format('d/m/Y') }}</div>
                    @endif
                </td>
                <td><span class="badge bg-{{ $statusClasses[$manutencao->status] ?? 'secondary' }}">{{ ['em_dia'=>'Em dia','proximo_vencimento'=>'Próximo do vencimento','vencido'=>'Vencido'][$manutencao->status] ?? $manutencao->status }}</span></td>
                <td><div class="sx-actions">
                    <button class="btn btn-icon btn-outline-light" type="button" data-bs-toggle="collapse" data-bs-target="#editar-manutencao-{{ $manutencao->id }}" title="Editar"><i class="bi bi-pencil-square"></i></button>
                    <form method="POST" action="{{ route('master.veiculos.manutencoes.destroy', [$veiculo, $manutencao]) }}" onsubmit="return confirm('Excluir este item de manutenção?')">@csrf @method('DELETE')<button class="btn btn-icon btn-danger" title="Excluir"><i class="bi bi-trash"></i></button></form>
                </div></td>
            </tr>
            <tr class="collapse" id="editar-manutencao-{{ $manutencao->id }}"><td colspan="5">
                <form method="POST" action="{{ route('master.veiculos.manutencoes.update', [$veiculo, $manutencao]) }}" class="row g-2">@csrf @method('PUT')
                    <div class="col-md-3"><input class="form-control" name="item" value="{{ $manutencao->item }}" required></div>
                    <div class="col-md-2"><input class="form-control" type="number" name="km_referencia" value="{{ $manutencao->km_referencia }}" required></div>
                    <div class="col-md-2"><input class="form-control" type="number" name="km_vencimento" value="{{ $manutencao->km_vencimento }}" required></div>
                    <div class="col-md-2"><input class="form-control" type="date" name="data_vencimento" value="{{ $manutencao->data_vencimento?->format('Y-m-d') }}"></div>
                    <div class="col-md-2"><input class="form-control" name="observacao" value="{{ $manutencao->observacao }}"></div>
                    <div class="col-md-1"><button class="btn btn-systex btn-sm">Salvar</button></div>
                </form>
            </td></tr>
        @endforeach
        </tbody></table></div>
        @else
            <div class="sx-muted">Nenhum item de manutenção cadastrado.</div>
        @endif
    </div>
</div>
@endsection
