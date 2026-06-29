@if($errors->any())
    <div class="alert alert-danger">@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>
@endif

<div class="sx-card">
    <form method="POST" action="{{ $action }}" class="row g-3">
        @csrf
        @if(($method ?? 'POST') !== 'POST') @method($method) @endif
        <div class="col-md-3">
            <label class="form-label">Placa *</label>
            <input class="form-control" name="placa" value="{{ old('placa', $veiculo->placa ?? '') }}" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">Modelo *</label>
            <input class="form-control" name="modelo" value="{{ old('modelo', $veiculo->modelo ?? '') }}" required>
        </div>
        <div class="col-md-2">
            <label class="form-label">Ano</label>
            <input class="form-control" type="number" name="ano" min="1900" max="{{ now()->year + 1 }}" value="{{ old('ano', $veiculo->ano ?? '') }}">
        </div>
        <div class="col-md-3">
            <label class="form-label">Tipo *</label>
            <select class="form-select" name="tipo" required>
                <option value="proprio" @selected(old('tipo', $veiculo->tipo ?? 'proprio') === 'proprio')>Próprio</option>
                <option value="parceiro" @selected(old('tipo', $veiculo->tipo ?? '') === 'parceiro')>Parceiro</option>
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label">Data do documento</label>
            <input class="form-control" type="date" name="data_documento" value="{{ old('data_documento', isset($veiculo) && $veiculo->data_documento ? $veiculo->data_documento->format('Y-m-d') : '') }}">
        </div>
        <div class="col-md-3">
            <label class="form-label">Km atual *</label>
            <input class="form-control" type="number" name="km_atual" min="0" value="{{ old('km_atual', $veiculo->km_atual ?? 0) }}" required>
        </div>
        <div class="col-md-3">
            <label class="form-label">Capacidade *</label>
            <input class="form-control" type="number" name="capacidade_passageiros" min="1" value="{{ old('capacidade_passageiros', $veiculo->capacidade_passageiros ?? 15) }}" required>
        </div>
        <div class="col-md-3">
            <label class="form-label">Status *</label>
            <select class="form-select" name="status_operacional" required>
                <option value="liberado" @selected(old('status_operacional', $veiculo->status_operacional ?? 'liberado') === 'liberado')>Liberado</option>
                <option value="bloqueado" @selected(old('status_operacional', $veiculo->status_operacional ?? '') === 'bloqueado')>Bloqueado</option>
            </select>
        </div>
        <div class="col-12 d-flex justify-content-end gap-2">
            <a class="btn btn-outline-light" href="{{ $cancelRoute }}">Cancelar</a>
            <button class="btn btn-systex"><i class="bi bi-check-circle"></i> {{ $submitLabel }}</button>
        </div>
    </form>
</div>
