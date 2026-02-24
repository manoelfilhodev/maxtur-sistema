@extends('layouts.app')

@section('page-heading')
    <div class="d-flex align-items-start justify-content-between flex-wrap gap-2">
        <div>
            <h3 class="fw-bold mb-1 text-white">Usuários</h3>
            <div class="text-muted" style="color: rgba(255,255,255,.65) !important;">
                Cadastro, permissões e manutenção de acessos
            </div>
        </div>

        <div class="d-flex gap-2">
            {{-- Excluir selecionados --}}
            <form id="formExcluirSelecionados" action="{{ route('usuarios.destroy.multiple') }}" method="POST" class="d-none">
                @csrf
                @method('DELETE')
                <input type="hidden" id="idsSelecionados" name="ids">
            </form>

            <button id="btnExcluirSelecionados" class="btn btn-outline-light btn-sm" disabled>
                <i class="bi bi-trash3 me-1"></i> Excluir selecionados
            </button>

            <a href="{{ route('usuarios.create') }}" class="btn btn-systex btn-sm">
                <i class="bi bi-plus-circle me-1"></i> Novo usuário
            </a>
        </div>
    </div>
@endsection

@section('content')
    <style>
    
    /* 🔥 NEUTRALIZA O BOOTSTRAP PARA ESSA TABELA */
.table-systex-grid {
  --bs-table-bg: transparent !important;
  --bs-table-accent-bg: transparent !important;
  --bs-table-striped-bg: transparent !important;
  --bs-table-hover-bg: transparent !important;
  color: rgba(255,255,255,.9);
}

/* Remove o overlay branco do Bootstrap */
.table-systex-grid > :not(caption) > * > * {
  background-color: transparent !important;
  box-shadow: none !important;
}


        /* =========================
           CARD / CONTAINER
        ========================== */
        .systex-users-wrap .dash-card{
            background: rgba(18, 18, 20, .72) !important;
            border: 1px solid rgba(255, 255, 255, .08) !important;
            border-radius: 16px !important;
            box-shadow: 0 16px 45px rgba(0, 0, 0, .35);
            color: #fff;
        }

        /* =========================
           BLINDAGEM: remove brancos herdados
           (isso resolve seu problema)
        ========================== */
        .systex-users-wrap .table,
        .systex-users-wrap .table-responsive,
        .systex-users-wrap .table-responsive *{
            background-color: transparent !important;
        }

        /* Wrapper da tabela */
        .systex-users-wrap .table-shell{
            border-radius: 16px;
            overflow: hidden;
            border: 1px solid rgba(255,255,255,.08);
            background: rgba(12,12,14,.72) !important;
        }

        /* =========================
           TABELA (super forte)
        ========================== */
        .systex-users-wrap table.table-systex-grid{
            width: 100%;
            margin: 0 !important;
            border-collapse: separate !important;
            border-spacing: 0 !important;
            background: rgba(12,12,14,.72) !important;
        }

        /* Header */
        .systex-users-wrap table.table-systex-grid thead th{
            background: rgba(255,255,255,.04) !important;
            color: rgba(255,255,255,.78) !important;
            border-bottom: 1px solid rgba(255,255,255,.08) !important;
            border-top: 0 !important;
            font-weight: 800;
            font-size: 12px;
            letter-spacing: .3px;
            text-transform: uppercase;
            white-space: nowrap;
            padding: 12px 12px !important;
        }

        /* Corpo: força fundo escuro em TODAS as camadas */
        .systex-users-wrap table.table-systex-grid tbody{
            background: rgba(12,12,14,.72) !important;
        }

        .systex-users-wrap table.table-systex-grid tbody tr{
            background: rgba(18,18,20,.58) !important;
        }

        .systex-users-wrap table.table-systex-grid tbody tr:nth-child(even){
            background: rgba(18,18,20,.46) !important;
        }

        .systex-users-wrap table.table-systex-grid tbody td{
            background: transparent !important; /* td pega o fundo do tr */
            color: rgba(255,255,255,.88) !important;
            border-top: 1px solid rgba(255,255,255,.06) !important;
            border-left: 0 !important;
            border-right: 0 !important;
            border-bottom: 0 !important;
            padding: 14px 12px !important;
            vertical-align: middle;
        }

        /* Hover profissional */
        .systex-users-wrap table.table-systex-grid tbody tr:hover{
            background: rgba(255,255,255,.05) !important;
        }

        .systex-users-wrap table.table-systex-grid tbody tr:hover td{
            background: transparent !important;
        }

        /* Textos secundários */
        .systex-users-wrap .muted{
            color: rgba(255,255,255,.64) !important;
        }

        /* Chip de nível */
        .systex-users-wrap .chip{
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 6px 10px;
            border-radius: 999px;
            font-weight: 800;
            font-size: 12px;
            border: 1px solid rgba(255,255,255,.10);
        }
        .systex-users-wrap .chip-admin{
            background: rgba(255,42,42,.12) !important;
            border-color: rgba(255,42,42,.22) !important;
            color: #ffd0d0 !important;
        }
        .systex-users-wrap .chip-gestor{
            background: rgba(250,204,21,.12) !important;
            border-color: rgba(250,204,21,.22) !important;
            color: #ffeaa6 !important;
        }
        .systex-users-wrap .chip-user{
            background: rgba(255,255,255,.06) !important;
            border-color: rgba(255,255,255,.12) !important;
            color: rgba(255,255,255,.82) !important;
        }

        /* Botões ícones */
        .systex-users-wrap .btn-icon{
            width: 34px;
            height: 34px;
            border-radius: 12px;
            display: inline-grid;
            place-items: center;
            padding: 0 !important;
        }

        .systex-users-wrap .btn-icon.btn-outline-light{
            border-color: rgba(255,255,255,.16) !important;
            color: rgba(255,255,255,.86) !important;
            background: rgba(255,255,255,.04) !important;
        }
        .systex-users-wrap .btn-icon.btn-outline-light:hover{
            background: rgba(255,255,255,.08) !important;
        }

        .systex-users-wrap .btn-icon.btn-danger{
            background: rgba(255,77,79,.12) !important;
            border: 1px solid rgba(255,77,79,.25) !important;
            color: #ffd0d0 !important;
        }
        .systex-users-wrap .btn-icon.btn-danger:hover{
            background: rgba(255,77,79,.18) !important;
        }

        /* Checkbox com contraste */
        .systex-users-wrap .form-check-input{
            cursor: pointer;
            background-color: rgba(255,255,255,.08) !important;
            border-color: rgba(255,255,255,.18) !important;
        }
        .systex-users-wrap .form-check-input:checked{
            background-color: #ff2a2a !important;
            border-color: #ff2a2a !important;
        }
    </style>

    <div class="container-fluid px-4 systex-users-wrap">

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show d-flex align-items-center" role="alert">
                <i class="bi bi-check-circle-fill fs-5 me-2"></i>
                <span>{{ session('success') }}</span>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="dash-card p-3">
            <div class="d-flex align-items-center justify-content-between mb-2 flex-wrap gap-2">
                <div class="muted small">
                    Total: <b class="text-white">{{ count($usuarios) }}</b> usuário(s)
                </div>
                <div class="muted small">
                    Dica: selecione usuários para habilitar exclusão em massa
                </div>
            </div>

            <div class="table-responsive table-shell">
                <table class="table table-hover mb-0 table-systex-grid">

                    <thead>
                        <tr>
                            <th width="44" class="text-center">
                                <input type="checkbox" id="selectAll" class="form-check-input">
                            </th>
                            <th>Nome</th>
                            <th>E-mail</th>
                            <th>CPF</th>
                            <th>Nível</th>
                            <th>Jornada</th>
                            <th>Cadastrado em</th>
                            <th width="120" class="text-center">Ações</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($usuarios as $u)
                            @php
                                $role = $u->cargo ?? $u->nivel ?? 'USUARIO';

                                $chipClass = match(strtoupper($role)) {
                                    'ADMIN' => 'chip-admin',
                                    'GESTOR' => 'chip-gestor',
                                    default => 'chip-user'
                                };
                            @endphp

                            <tr>
                                <td class="text-center">
                                    <input type="checkbox" class="checkboxUser form-check-input" value="{{ $u->id }}">
                                </td>

                                <td class="fw-semibold">{{ $u->name }}</td>
                                <td class="muted">{{ $u->email }}</td>
                                <td class="muted">{{ $u->cpf }}</td>

                                <td>
                                    <span class="chip {{ $chipClass }}">
                                        <i class="bi bi-shield-lock"></i> {{ strtoupper($role) }}
                                    </span>
                                </td>
                                <td class="muted">
                                {{ $u->jornada_id ?? '—' }}
                                @if(!empty($u->turno_id))
                                  <span class="badge-soft ms-2">{{ $u->turno_id }}</span>
                                @endif
                              </td>

                                <td class="muted">{{ $u->created_at->format('d/m/Y') }}</td>

                                <td class="text-center">
                                    <div class="d-flex justify-content-center gap-2">
                                        <a href="{{ route('usuarios.edit', $u->id) }}"
                                           class="btn btn-icon btn-outline-light"
                                           data-bs-toggle="tooltip" title="Editar">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>

                                        <form action="{{ route('usuarios.destroy', $u->id) }}"
                                              method="POST"
                                              onsubmit="return confirm('Deseja excluir este usuário?')">
                                            @csrf
                                            @method('DELETE')

                                            <button class="btn btn-icon btn-danger"
                                                    data-bs-toggle="tooltip" title="Excluir">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>

                        @empty
                            <tr>
                                <td colspan="7" class="text-center muted py-4">
                                    Nenhum usuário encontrado.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>

                </table>
            </div>
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
            ? `<i class="bi bi-trash3 me-1"></i> Excluir (${selecionados})`
            : `<i class="bi bi-trash3 me-1"></i> Excluir selecionados`;
    }

    if (selectAll) {
        selectAll.addEventListener('change', function () {
            checkboxes.forEach(c => c.checked = selectAll.checked);
            atualizarBotao();
        });
    }

    checkboxes.forEach(c => c.addEventListener('change', atualizarBotao));

    btnExcluir.addEventListener('click', function () {
        const ids = [...checkboxes].filter(c => c.checked).map(c => c.value);
        if (ids.length === 0) return;
        if (!confirm(`Deseja excluir ${ids.length} usuário(s)?`)) return;

        idsSelecionadosInput.value = JSON.stringify(ids);
        document.getElementById('formExcluirSelecionados').submit();
    });

    atualizarBotao();
</script>
@endsection
