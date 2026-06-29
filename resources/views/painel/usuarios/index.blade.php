@extends('layouts.app')

@section('page-heading')
    <div class="sx-page-header">
        <div>
            <h3 class="sx-page-title">Usuarios</h3>
            <div class="sx-page-subtitle">Cadastro, permissoes e manutencao de acessos</div>
        </div>
        <div class="sx-page-actions">
            <form id="formExcluirSelecionados" action="{{ route('usuarios.destroy.multiple') }}" method="POST" class="d-none">
                @csrf
                @method('DELETE')
                <input type="hidden" id="idsSelecionados" name="ids">
            </form>
            <button id="btnExcluirSelecionados" class="btn btn-outline-light btn-sm" disabled>
                <i class="bi bi-trash3"></i> Excluir selecionados
            </button>
            <a href="{{ route('usuarios.create') }}" class="btn btn-systex btn-sm">
                <i class="bi bi-plus-circle"></i> Novo usuario
            </a>
        </div>
    </div>
@endsection

@section('content')
<div class="sx-container">
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center" role="alert">
            <i class="bi bi-check-circle-fill fs-5 me-2"></i>
            <span>{{ session('success') }}</span>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="sx-card">
        <div class="alert alert-info mb-3"><strong>Perfis de acesso:</strong> Admin gerencia cadastros, viagens, relatórios e configurações; Gestor atua na operação; Cliente acessa informações vinculadas ao seu cliente; Motorista acessa viagens atribuídas e rotinas operacionais; Usuário possui acesso restrito às funções liberadas ao cliente.</div>
        <div class="sx-card-header">
            <div>
                <h5 class="sx-card-title">Acessos cadastrados</h5>
                <div class="sx-muted small">Total: <b class="text-white">{{ count($usuarios) }}</b> usuario(s)</div>
            </div>
            <div class="sx-muted small">Selecione usuarios para habilitar exclusao em massa</div>
        </div>

        @if(count($usuarios))
            <div class="table-responsive sx-table-shell">
                <table class="table table-hover table-systex-grid">
                    <thead>
                        <tr>
                            <th width="44" class="text-center"><input type="checkbox" id="selectAll" class="form-check-input"></th>
                            <th>Nome</th>
                            <th>E-mail</th>
                            <th>CPF</th>
                            <th>Nivel</th>
                            <th>Jornada</th>
                            <th>Cadastrado em</th>
                            <th class="text-end">Acoes</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($usuarios as $u)
                        @php
                            $role = $u->nivel ?? $u->cargo ?? $u->role ?? 'USUARIO';
                            $chipClass = match(strtoupper($role)) {
                                'ADMIN' => 'sx-badge-danger',
                                'GESTOR' => 'sx-badge-warning',
                                default => 'sx-badge-info'
                            };
                        @endphp
                        <tr>
                            <td class="text-center"><input type="checkbox" class="checkboxUser form-check-input" value="{{ $u->id }}"></td>
                            <td class="fw-semibold">{{ $u->name }}</td>
                            <td class="sx-muted">{{ $u->email }}</td>
                            <td class="sx-muted">{{ $u->cpf }}</td>
                            <td><span class="sx-badge {{ $chipClass }}"><i class="bi bi-shield-lock"></i> {{ strtoupper($role) }}</span></td>
                            <td class="sx-muted">
                                {{ $u->jornada_id ?? '-' }}
                                @if(!empty($u->turno_id))
                                    <span class="badge-soft ms-2">{{ $u->turno_id }}</span>
                                @endif
                            </td>
                            <td class="sx-muted">{{ $u->created_at->format('d/m/Y') }}</td>
                            <td>
                                <div class="sx-actions">
                                    <a href="{{ route('usuarios.edit', $u->id) }}" class="btn btn-icon btn-outline-light" data-bs-toggle="tooltip" title="Editar">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                    <form action="{{ route('usuarios.destroy', $u->id) }}" method="POST" onsubmit="return confirm('Deseja excluir este usuario?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-icon btn-danger" data-bs-toggle="tooltip" title="Excluir">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        @else
            @include('partials.panel.empty-state', [
                'title' => 'Nenhum usuario encontrado',
                'message' => 'Cadastre o primeiro usuario para configurar os acessos da operacao.',
                'actionRoute' => route('usuarios.create'),
                'actionLabel' => 'Novo usuario',
                'icon' => 'bi bi-people',
            ])
        @endif
    </div>
</div>
@endsection

@section('scripts')
<script>
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.checkboxUser');
    const btnExcluir = document.getElementById('btnExcluirSelecionados');
    const idsSelecionadosInput = document.getElementById('idsSelecionados');

    function atualizarBotao() {
        const selecionados = [...checkboxes].filter(c => c.checked).length;
        btnExcluir.disabled = selecionados === 0;
        btnExcluir.innerHTML = selecionados > 0
            ? `<i class="bi bi-trash3"></i> Excluir (${selecionados})`
            : `<i class="bi bi-trash3"></i> Excluir selecionados`;
    }

    if (selectAll) {
        selectAll.addEventListener('change', function () {
            checkboxes.forEach(c => c.checked = selectAll.checked);
            atualizarBotao();
        });
    }

    checkboxes.forEach(c => c.addEventListener('change', atualizarBotao));

    if (btnExcluir) {
        btnExcluir.addEventListener('click', function () {
            const ids = [...checkboxes].filter(c => c.checked).map(c => c.value);
            if (ids.length === 0) return;
            if (!confirm(`Deseja excluir ${ids.length} usuario(s)?`)) return;

            idsSelecionadosInput.value = JSON.stringify(ids);
            document.getElementById('formExcluirSelecionados').submit();
        });
    }

    atualizarBotao();
</script>
@endsection
