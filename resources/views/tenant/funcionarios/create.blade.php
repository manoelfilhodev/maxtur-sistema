@extends('layouts.app')

@section('page-heading')
<div class="d-flex align-items-start justify-content-between flex-wrap gap-2">
    <div>
        <h3 class="fw-bold mb-1 text-white">Cadastro em lote de funcionários</h3>
        <div class="text-muted" style="color: rgba(255,255,255,.65) !important;">
            Adicione múltiplos funcionários de uma vez e envie links de ativação individuais
        </div>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('tenant.funcionarios.import.form') }}" class="btn btn-outline-light btn-sm">
            <i class="bi bi-upload me-1"></i> Importar funcionários (CSV)
        </a>
        <a href="{{ route('tenant.funcionarios.index') }}" class="btn btn-outline-light btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Voltar
        </a>
    </div>
</div>
@endsection

@section('content')
<div class="dash-card p-3 p-lg-4">
    @if ($errors->any())
        <div class="alert alert-danger">
            @foreach($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <form id="bulk-form" method="POST" action="{{ route('tenant.funcionarios.store-multiple') }}">
        @csrf

        <div class="table-responsive">
            <table class="table table-dark table-hover align-middle" id="funcionarios-table">
                <thead>
                <tr>
                    <th style="min-width: 220px;">Nome *</th>
                    <th style="min-width: 220px;">E-mail *</th>
                    <th style="min-width: 180px;">Cargo</th>
                    <th style="min-width: 160px;">Telefone</th>
                    <th style="min-width: 280px;">Endereço</th>
                    <th class="text-center" style="width: 90px;">Ações</th>
                </tr>
                </thead>
                <tbody>
                @php
                    $oldRows = old('funcionarios', [['name' => '', 'email' => '', 'cargo' => '', 'telefone' => '', 'endereco' => '']]);
                @endphp
                @foreach($oldRows as $i => $row)
                    <tr>
                        <td><input class="form-control" name="funcionarios[{{ $i }}][name]" value="{{ $row['name'] ?? '' }}" placeholder="Nome completo"></td>
                        <td><input class="form-control" type="email" name="funcionarios[{{ $i }}][email]" value="{{ $row['email'] ?? '' }}" placeholder="email@empresa.com"></td>
                        <td><input class="form-control" name="funcionarios[{{ $i }}][cargo]" value="{{ $row['cargo'] ?? '' }}" placeholder="Ex.: Analista"></td>
                        <td><input class="form-control" name="funcionarios[{{ $i }}][telefone]" value="{{ $row['telefone'] ?? '' }}" placeholder="(00) 00000-0000"></td>
                        <td><input class="form-control" name="funcionarios[{{ $i }}][endereco]" value="{{ $row['endereco'] ?? '' }}" placeholder="Rua, número, bairro, cidade"></td>
                        <td class="text-center">
                            <button type="button" class="btn btn-outline-light btn-sm remove-row" title="Remover linha">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>

        <div class="d-flex flex-wrap gap-2 mt-3">
            <button type="button" id="add-row" class="btn btn-outline-light">
                <i class="bi bi-plus-circle me-1"></i> Adicionar outro funcionário
            </button>
            <button type="submit" id="save-bulk-btn" class="btn btn-systex">
                <span class="btn-label"><i class="bi bi-check2-circle me-1"></i> Cadastrar em lote</span>
                <span class="btn-loading d-none"><span class="spinner-border spinner-border-sm me-2"></span>Processando...</span>
            </button>
        </div>
    </form>
</div>

<template id="funcionario-row-template">
    <tr>
        <td><input class="form-control" name="__NAME__" placeholder="Nome completo"></td>
        <td><input class="form-control" type="email" name="__EMAIL__" placeholder="email@empresa.com"></td>
        <td><input class="form-control" name="__CARGO__" placeholder="Ex.: Analista"></td>
        <td><input class="form-control" name="__PHONE__" placeholder="(00) 00000-0000"></td>
        <td><input class="form-control" name="__ADDRESS__" placeholder="Rua, número, bairro, cidade"></td>
        <td class="text-center">
            <button type="button" class="btn btn-outline-light btn-sm remove-row" title="Remover linha">
                <i class="bi bi-trash"></i>
            </button>
        </td>
    </tr>
</template>
@endsection

@push('scripts')
<script>
    (function () {
        const tableBody = document.querySelector('#funcionarios-table tbody');
        const addRowBtn = document.getElementById('add-row');
        const rowTemplate = document.getElementById('funcionario-row-template').innerHTML.trim();
        const form = document.getElementById('bulk-form');
        const saveBtn = document.getElementById('save-bulk-btn');

        function nextIndex() {
            return tableBody.querySelectorAll('tr').length;
        }

        function bindRemoveButtons() {
            tableBody.querySelectorAll('.remove-row').forEach(function (btn) {
                btn.onclick = function () {
                    const rows = tableBody.querySelectorAll('tr');
                    if (rows.length === 1) {
                        rows[0].querySelectorAll('input').forEach(function (input) { input.value = ''; });
                        return;
                    }
                    btn.closest('tr').remove();
                };
            });
        }

        addRowBtn.addEventListener('click', function () {
            const index = nextIndex();
            const html = rowTemplate
                .replace('__NAME__', `funcionarios[${index}][name]`)
                .replace('__EMAIL__', `funcionarios[${index}][email]`)
                .replace('__CARGO__', `funcionarios[${index}][cargo]`)
                .replace('__PHONE__', `funcionarios[${index}][telefone]`)
                .replace('__ADDRESS__', `funcionarios[${index}][endereco]`);
            tableBody.insertAdjacentHTML('beforeend', html);
            bindRemoveButtons();
        });

        form.addEventListener('submit', function () {
            saveBtn.disabled = true;
            saveBtn.querySelector('.btn-label').classList.add('d-none');
            saveBtn.querySelector('.btn-loading').classList.remove('d-none');
        });

        bindRemoveButtons();
    })();
</script>
@endpush

