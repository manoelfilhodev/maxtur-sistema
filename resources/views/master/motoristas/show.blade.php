@extends('layouts.app')

@section('page-heading')
    <div class="sx-page-header">
        <div>
            <h3 class="sx-page-title">{{ $motorista->name }}</h3>
            <div class="sx-page-subtitle">Perfil operacional do motorista</div>
        </div>
        <div class="sx-page-actions">
            <a class="btn btn-outline-light btn-sm" href="{{ route('master.motoristas.index') }}">
                <i class="bi bi-arrow-left"></i> Voltar
            </a>
            <a class="btn btn-systex btn-sm" href="{{ route('master.motoristas.edit', $motorista->id) }}">
                <i class="bi bi-pencil-square"></i> Editar
            </a>
        </div>
    </div>
@endsection

@section('content')
<div class="sx-container">
    @include('partials.panel.flash-messages')
    @include('partials.panel.form-errors')

    <div class="sx-card">
        <div class="row g-3">
            <div class="col-md-6">
                <div class="sx-kicker">E-mail</div>
                <div class="text-white">{{ $motorista->email }}</div>
            </div>
            <div class="col-md-3">
                <div class="sx-kicker">Telefone</div>
                <div class="text-white">{{ $motorista->telefone ?: '-' }}</div>
            </div>
            <div class="col-md-3">
                <div class="sx-kicker">Documento/CNH</div>
                <div class="text-white">{{ $motorista->cpf ?: '-' }}</div>
            </div>
            <div class="col-md-3">
                <div class="sx-kicker">Status</div>
                @include('partials.panel.status-badge', ['status' => $motorista->ativo ? 'ativo' : 'inativo'])
            </div>
            <div class="col-md-3">
                <div class="sx-kicker">Perfil</div>
                <span class="sx-badge sx-badge-info">{{ strtoupper($motorista->role ?? 'MOTORISTA') }}</span>
            </div>
            <div class="col-md-3"><div class="sx-kicker">Vencimento da CNH</div>@if($motorista->cnh_vencimento) @php($diasCnh = now()->startOfDay()->diffInDays($motorista->cnh_vencimento, false)) <span class="badge bg-{{ $diasCnh < 0 ? 'danger' : ($diasCnh <= 30 ? 'warning' : 'success') }}">{{ $motorista->cnh_vencimento->format('d/m/Y') }}</span> @else <div class="text-white">-</div> @endif</div>
            <div class="col-md-3"><div class="sx-kicker">Admissão</div><div class="text-white">{{ $motorista->data_admissao?->format('d/m/Y') ?: '-' }}</div></div>
            <div class="col-md-3"><div class="sx-kicker">Recebimento</div><div class="text-white">{{ $motorista->tipo_recebimento === 'por_viagem' ? 'Por viagem' : 'Salário' }}</div></div>
            <div class="col-md-3"><div class="sx-kicker">Valor</div><div class="text-white">R$ {{ number_format((float) ($motorista->tipo_recebimento === 'por_viagem' ? $motorista->valor_por_viagem : $motorista->valor_salario), 2, ',', '.') }}</div></div>
        </div>
    </div>

    <div class="sx-card mt-3">
        <div class="sx-card-header"><div><h5 class="sx-card-title">Documentos</h5><div class="sx-muted small">PDF, JPG, JPEG ou PNG, até 5 MB. Novo envio do mesmo tipo substitui o anterior.</div></div></div>
        <form method="POST" enctype="multipart/form-data" action="{{ route('master.motoristas.documentos.store', $motorista) }}" class="row g-2 align-items-end mb-3">@csrf
            <div class="col-md-4"><label class="form-label">Tipo</label><select class="form-select" name="tipo" required><option value="cnh">CNH</option><option value="documento_pessoal">Documento pessoal</option><option value="comprovante_endereco">Comprovante de endereço</option><option value="outro">Outro</option></select></div>
            <div class="col-md-6"><label class="form-label">Arquivo</label><input class="form-control" type="file" name="documento" accept=".pdf,.jpg,.jpeg,.png" required></div>
            <div class="col-md-2"><button class="btn btn-systex w-100"><i class="bi bi-paperclip"></i> Anexar</button></div>
        </form>
        @if($motorista->documentosMotorista->count())
        <div class="table-responsive sx-table-shell"><table class="table table-hover table-systex-grid"><thead><tr><th>Tipo</th><th>Arquivo</th><th>Tamanho</th><th class="text-end">Ações</th></tr></thead><tbody>
            @foreach($motorista->documentosMotorista as $documento)<tr><td>{{ ['cnh'=>'CNH','documento_pessoal'=>'Documento pessoal','comprovante_endereco'=>'Comprovante de endereço','outro'=>'Outro'][$documento->tipo] }}</td><td>{{ $documento->nome_original }}</td><td>{{ number_format($documento->tamanho / 1024, 0, ',', '.') }} KB</td><td><div class="sx-actions"><a class="btn btn-icon btn-outline-light" href="{{ route('master.motoristas.documentos.download', [$motorista, $documento]) }}" title="Baixar"><i class="bi bi-download"></i></a><form method="POST" action="{{ route('master.motoristas.documentos.destroy', [$motorista, $documento]) }}" onsubmit="return confirm('Excluir este documento?')">@csrf @method('DELETE')<button class="btn btn-icon btn-danger"><i class="bi bi-trash"></i></button></form></div></td></tr>@endforeach
        </tbody></table></div>
        @else <div class="sx-muted">Nenhum documento anexado.</div> @endif
    </div>
</div>
@endsection
