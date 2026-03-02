@extends('layouts.app')

@section('page-heading')
<div class="d-flex align-items-start justify-content-between flex-wrap gap-2">
    <div>
        <h3 class="fw-bold mb-1 text-white">Funcionários</h3>
        <div class="text-muted" style="color: rgba(255,255,255,.65) !important;">
            Gestão de usuários do seu cliente
        </div>
    </div>
    <div class="d-flex gap-2">
        <a class="btn btn-outline-light btn-sm" href="{{ route('tenant.funcionarios.import.form') }}">
            <i class="bi bi-upload me-1"></i> Importar arquivo
        </a>
        <a class="btn btn-systex btn-sm" href="{{ route('tenant.funcionarios.create') }}">
            <i class="bi bi-plus-circle me-1"></i> Cadastro em lote
        </a>
    </div>
</div>
@endsection

@section('content')
<style>
.table-shell{border-radius:16px;overflow:hidden;border:1px solid rgba(255,255,255,.08);background:rgba(12,12,14,.72)!important}
.table-shell .table{margin-bottom:0!important}
</style>

@php
    $bulk = session('bulk_results');
@endphp

@if($bulk)
    <div class="dash-card p-3 mb-3">
        <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
            <span class="badge bg-success">Sucesso: {{ $bulk['created_count'] ?? 0 }}</span>
            <span class="badge bg-danger">Erros: {{ $bulk['error_count'] ?? 0 }}</span>
            @if(isset($bulk['total_read']))
                <span class="badge bg-secondary">Lidos: {{ $bulk['total_read'] }}</span>
            @endif
        </div>
        <div class="text-white fw-semibold">{{ $bulk['message'] ?? 'Processamento concluído.' }}</div>

        @if(!empty($bulk['errors']))
            <div class="alert alert-danger mt-3 mb-0">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-1">
                    <span class="fw-semibold">Falhas no processamento</span>
                    <a href="{{ route('tenant.funcionarios.import.errors') }}" class="btn btn-outline-light btn-sm">
                        Baixar relatório de erros
                    </a>
                </div>
                @foreach($bulk['errors'] as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif
    </div>
@endif

<div class="dash-card p-3">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
        <div class="text-muted small">Total: <b class="text-white">{{ $funcionarios->total() }}</b></div>
        <div class="d-flex gap-2">
            <button form="bulk-invite-form" id="bulk-send-btn" class="btn btn-systex btn-sm" type="submit" disabled>
                <i class="bi bi-envelope-paper me-1"></i> Enviar convites
            </button>
            <button id="bulk-delete-open-btn" class="btn btn-outline-light btn-sm" type="button" disabled>
                <i class="bi bi-trash me-1"></i> Excluir selecionados
            </button>
        </div>
    </div>

    <form id="bulk-invite-form" method="POST" action="{{ route('tenant.funcionarios.send-invite-bulk') }}">
        @csrf
    </form>
    <form id="bulk-delete-form" method="POST" action="{{ route('tenant.funcionarios.destroy-bulk') }}">
        @csrf
        @method('DELETE')
        <div id="bulk-delete-hidden-inputs"></div>
        <input type="hidden" name="password" id="bulk-delete-password">
    </form>

    <div class="table-responsive table-shell">
        <table class="table table-dark table-hover">
            <thead>
                <tr>
                    <th style="width:40px;">
                        <input type="checkbox" id="check-all">
                    </th>
                    <th>Nome</th>
                    <th>E-mail</th>
                    <th>Perfil</th>
                    <th>Status</th>
                    <th class="text-center">Ações</th>
                </tr>
            </thead>
            <tbody>
            @forelse($funcionarios as $funcionario)
                @php
                    $pendente = is_null($funcionario->activated_at);
                    $activationLink = $funcionario->activation_token ? route('activation.show', $funcionario->activation_token) : null;
                    $waPhone = preg_replace('/\D+/', '', (string) ($funcionario->telefone ?? ''));
                    $waMessage = rawurlencode("Olá {$funcionario->name}, ative seu acesso no Systex Mobility: {$activationLink}");
                    $waLink = $waPhone ? "https://wa.me/55{$waPhone}?text={$waMessage}" : "https://wa.me/?text={$waMessage}";
                @endphp
                <tr>
                    <td>
                        <input type="checkbox" class="invite-checkbox" name="funcionarios[]" value="{{ $funcionario->id }}" form="bulk-invite-form">
                    </td>
                    <td class="fw-semibold">{{ $funcionario->name }}</td>
                    <td>{{ $funcionario->email }}</td>
                    <td><span class="badge bg-secondary">{{ $funcionario->role }}</span></td>
                    <td>
                        @if($pendente)
                            <span class="badge bg-warning text-dark">Pendente</span>
                        @else
                            <span class="badge bg-success">Ativado</span>
                        @endif
                    </td>
                    <td class="text-center d-flex justify-content-center gap-1 flex-wrap">
                        <a class="btn btn-outline-light btn-sm" href="{{ route('tenant.funcionarios.show', $funcionario->id) }}">Ver</a>

                        @if($activationLink && $pendente)
                            <button
                                class="btn btn-outline-light btn-sm js-copy-link"
                                data-label="Copiar link"
                                data-link="{{ $activationLink }}"
                                type="button"
                            >
                                Copiar link
                            </button>

                            <a class="btn btn-outline-light btn-sm" href="{{ $waLink }}" target="_blank" rel="noopener">
                                WhatsApp
                            </a>

                            <form method="POST" action="{{ route('tenant.funcionarios.send-invite', $funcionario->id) }}">
                                @csrf
                                <button class="btn btn-outline-light btn-sm" type="submit">Enviar e-mail</button>
                            </form>
                        @endif

                        <form method="POST" action="{{ route('tenant.funcionarios.regenerate-activation', $funcionario->id) }}">
                            @csrf
                            <button class="btn btn-outline-light btn-sm" type="submit">Regenerar link</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-muted py-4">Sem funcionários cadastrados.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3 d-flex justify-content-end">{{ $funcionarios->links() }}</div>
</div>

<div class="modal fade" id="bulkDeleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content" style="background:#111319;color:#fff;border:1px solid rgba(255,255,255,.1);">
            <div class="modal-header border-0">
                <h5 class="modal-title">Confirmar exclusão em massa</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted mb-2">Digite sua senha para confirmar a exclusão dos funcionários selecionados.</p>
                <input type="password" id="bulk-delete-password-input" class="form-control" placeholder="Sua senha">
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-outline-light btn-sm" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" id="bulk-delete-confirm-btn" class="btn btn-danger btn-sm">Confirmar exclusão</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    (function () {
        const checkAll = document.getElementById('check-all');
        const bulkButton = document.getElementById('bulk-send-btn');
        const bulkDeleteOpenBtn = document.getElementById('bulk-delete-open-btn');
        const bulkDeleteForm = document.getElementById('bulk-delete-form');
        const bulkDeleteInputs = document.getElementById('bulk-delete-hidden-inputs');
        const bulkDeletePassword = document.getElementById('bulk-delete-password');
        const bulkDeletePasswordInput = document.getElementById('bulk-delete-password-input');
        const bulkDeleteConfirmBtn = document.getElementById('bulk-delete-confirm-btn');
        const checkboxes = Array.from(document.querySelectorAll('.invite-checkbox'));
        const modalEl = document.getElementById('bulkDeleteModal');
        const modal = window.bootstrap && modalEl ? new bootstrap.Modal(modalEl) : null;

        function refreshBulkButton() {
            const hasChecked = checkboxes.some(cb => cb.checked);
            bulkButton.disabled = !hasChecked;
            bulkDeleteOpenBtn.disabled = !hasChecked;
        }

        if (checkAll) {
            checkAll.addEventListener('change', function () {
                checkboxes.forEach(cb => { cb.checked = checkAll.checked; });
                refreshBulkButton();
            });
        }

        checkboxes.forEach(cb => cb.addEventListener('change', refreshBulkButton));
        refreshBulkButton();

        function selectedIds() {
            return checkboxes.filter(cb => cb.checked).map(cb => cb.value);
        }

        bulkDeleteOpenBtn.addEventListener('click', function () {
            if (!selectedIds().length) return;
            bulkDeletePasswordInput.value = '';
            if (modal) {
                modal.show();
            }
        });

        bulkDeleteConfirmBtn.addEventListener('click', function () {
            const ids = selectedIds();
            const pwd = bulkDeletePasswordInput.value || '';
            if (!ids.length) return;
            if (!pwd) {
                bulkDeletePasswordInput.focus();
                return;
            }

            bulkDeleteInputs.innerHTML = '';
            ids.forEach(function (id) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'funcionarios[]';
                input.value = id;
                bulkDeleteInputs.appendChild(input);
            });
            bulkDeletePassword.value = pwd;
            bulkDeleteForm.submit();
        });

        document.querySelectorAll('.js-copy-link').forEach(function (button) {
            button.addEventListener('click', async function () {
                const link = button.getAttribute('data-link');
                if (!link) return;

                try {
                    await navigator.clipboard.writeText(link);
                    const originalLabel = button.getAttribute('data-label') || 'Copiar link';
                    button.textContent = 'Link copiado';
                    setTimeout(() => { button.textContent = originalLabel; }, 1200);
                } catch (e) {
                    prompt('Copie o link manualmente:', link);
                }
            });
        });
    })();
</script>
@endpush

