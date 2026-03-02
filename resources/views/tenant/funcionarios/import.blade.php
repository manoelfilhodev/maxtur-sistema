@extends('layouts.app')

@section('page-heading')
<div class="d-flex align-items-start justify-content-between flex-wrap gap-2">
    <div>
        <h3 class="fw-bold mb-1 text-white">Importar funcionários por arquivo</h3>
        <div class="text-muted" style="color: rgba(255,255,255,.65) !important;">
            Aceita CSV e XLSX com colunas: nome,email,cargo,telefone,endereco
        </div>
    </div>
    <a href="{{ route('tenant.funcionarios.create') }}" class="btn btn-outline-light btn-sm">
        <i class="bi bi-arrow-left me-1"></i> Voltar ao cadastro em lote
    </a>
</div>
@endsection

@section('content')
<div class="dash-card p-3 p-lg-4 mb-3">
    @if ($errors->any())
        <div class="alert alert-danger">
            @foreach($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <div class="d-flex flex-wrap gap-2 mb-3">
        <a class="btn btn-outline-light btn-sm" href="{{ route('tenant.funcionarios.import.template.csv') }}">
            <i class="bi bi-download me-1"></i> Baixar modelo CSV
        </a>
        <a class="btn btn-outline-light btn-sm" href="{{ route('tenant.funcionarios.import.template.xlsx') }}">
            <i class="bi bi-download me-1"></i> Baixar modelo XLSX
        </a>
    </div>

    <form id="import-form" method="POST" action="{{ route('tenant.funcionarios.import') }}" enctype="multipart/form-data">
        @csrf
        <div class="mb-3">
            <label class="form-label text-white">Arquivo</label>
            <input id="arquivo-input" type="file" name="arquivo" class="form-control" accept=".csv,.xlsx,text/csv,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" required>
            <div class="form-text text-muted">Tamanho máximo: 4MB.</div>
            <div class="form-text text-muted">Dica: para endereços com vírgula, use aspas no CSV.</div>
        </div>

        <div id="file-validation-box" class="alert d-none mb-3"></div>

        <button id="import-btn" class="btn btn-systex" disabled>
            <span class="btn-label"><i class="bi bi-upload me-1"></i> Importar funcionários</span>
            <span class="btn-loading d-none"><span class="spinner-border spinner-border-sm me-2"></span>Processando arquivo...</span>
        </button>
    </form>
</div>

<div class="dash-card p-3">
    <h5 class="text-white mb-2">Como montar a planilha</h5>
    <ol class="mb-3 text-white-50">
        <li>Baixe o modelo CSV ou XLSX.</li>
        <li>Mantenha a primeira linha com os nomes das colunas.</li>
        <li>Preencha uma linha por funcionário.</li>
        <li>Não altere os nomes das colunas.</li>
    </ol>

    <h6 class="text-white">Exemplo</h6>
    <pre class="mb-0" style="background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.1);padding:12px;border-radius:12px;color:rgba(255,255,255,.9)">nome,email,cargo,telefone,endereco
Carlos Silva,carlos.silva@cliente.com,Operador,11999990001,"Rua Alfa, 123 - Centro, Cajamar - SP"
Mariana Souza,mariana.souza@cliente.com,Assistente,11999990002,"Av Beta, 456 - Jordanesia, Cajamar - SP"</pre>
</div>
@endsection

@push('scripts')
<script>
    (function () {
        const input = document.getElementById('arquivo-input');
        const form = document.getElementById('import-form');
        const btn = document.getElementById('import-btn');
        const box = document.getElementById('file-validation-box');
        const maxSize = 4 * 1024 * 1024;
        const validCsvHeaders = [
            'nome,email,cargo,telefone',
            'nome,email,cargo,telefone,endereco'
        ];

        function showBox(type, message) {
            box.classList.remove('d-none', 'alert-success', 'alert-danger', 'alert-warning');
            box.classList.add(type);
            box.textContent = message;
        }

        function disableSubmit(message) {
            btn.disabled = true;
            if (message) showBox('alert-danger', message);
        }

        function enableSubmit(message, warning) {
            btn.disabled = false;
            showBox(warning ? 'alert-warning' : 'alert-success', message);
        }

        function parseCsvLine(line) {
            const out = [];
            let cur = '';
            let q = false;
            for (let i = 0; i < line.length; i++) {
                const ch = line[i];
                const next = line[i + 1];
                if (ch === '"') {
                    if (q && next === '"') {
                        cur += '"';
                        i++;
                    } else {
                        q = !q;
                    }
                    continue;
                }
                if (ch === ',' && !q) {
                    out.push(cur);
                    cur = '';
                } else {
                    cur += ch;
                }
            }
            out.push(cur);
            return out.map(v => v.trim());
        }

        function validateCsv(file) {
            const reader = new FileReader();
            reader.onload = function () {
                const text = String(reader.result || '');
                const lines = text.replace(/\r/g, '').split('\n').filter(l => l.trim() !== '');
                if (!lines.length) {
                    disableSubmit('Arquivo CSV vazio.');
                    return;
                }

                const header = lines[0].replace(/^\uFEFF/, '').trim().toLowerCase();
                if (!validCsvHeaders.includes(header)) {
                    disableSubmit('Cabeçalho inválido. Use: nome,email,cargo,telefone[,endereco].');
                    return;
                }

                if (lines.length < 2) {
                    disableSubmit('Inclua pelo menos uma linha de dados.');
                    return;
                }

                for (let i = 1; i < lines.length; i++) {
                    const parts = parseCsvLine(lines[i]);
                    if (parts.length < 4) {
                        disableSubmit(`Linha ${i + 1}: colunas insuficientes.`);
                        return;
                    }
                    const email = (parts[1] || '').trim();
                    if (!email || !email.includes('@')) {
                        disableSubmit(`Linha ${i + 1}: email inválido.`);
                        return;
                    }
                }

                enableSubmit('Arquivo CSV validado. Pronto para importar.', false);
            };
            reader.onerror = function () {
                disableSubmit('Não foi possível ler o arquivo CSV.');
            };
            reader.readAsText(file, 'UTF-8');
        }

        input.addEventListener('change', function () {
            const file = input.files && input.files[0];
            if (!file) {
                disableSubmit(null);
                box.classList.add('d-none');
                return;
            }

            if (file.size > maxSize) {
                disableSubmit('Arquivo acima de 4MB.');
                return;
            }

            const name = file.name.toLowerCase();
            if (name.endsWith('.csv') || name.endsWith('.txt')) {
                validateCsv(file);
                return;
            }

            if (name.endsWith('.xlsx')) {
                enableSubmit('Arquivo XLSX selecionado. Estrutura será validada no envio.', true);
                return;
            }

            disableSubmit('Formato não suportado. Use CSV ou XLSX.');
        });

        form.addEventListener('submit', function (e) {
            if (btn.disabled) {
                e.preventDefault();
                return;
            }
            btn.disabled = true;
            btn.querySelector('.btn-label').classList.add('d-none');
            btn.querySelector('.btn-loading').classList.remove('d-none');
        });
    })();
</script>
@endpush

