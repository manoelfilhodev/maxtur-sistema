<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Mail\FuncionarioActivationInviteMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use ZipArchive;

class FuncionarioController extends Controller
{
    private const EMPLOYEE_ROLES = ['CLIENT_USER', 'funcionario'];

    public function index(Request $request)
    {
        $clientId = (int) $request->attributes->get('client_id');

        $funcionarios = User::query()
            ->where('client_id', $clientId)
            ->whereIn('role', self::EMPLOYEE_ROLES)
            ->orderBy('name')
            ->paginate(20);

        return view('tenant.funcionarios.index', compact('funcionarios'));
    }

    public function create()
    {
        return view('tenant.funcionarios.create');
    }

    public function showImport()
    {
        return view('tenant.funcionarios.import');
    }

    public function downloadTemplateCsv()
    {
        $csv = implode("\n", [
            'nome,email,cargo,telefone,endereco',
            'Carlos Silva,carlos.silva@cliente.com,Operador,11999990001,"Rua Alfa, 123 - Centro, Cajamar - SP"',
            'Mariana Souza,mariana.souza@cliente.com,Assistente,11999990002,"Av Beta, 456 - Jordanesia, Cajamar - SP"',
        ]);

        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="modelo_funcionarios.csv"',
        ]);
    }

    public function downloadTemplateXlsx()
    {
        $binary = $this->buildXlsxTemplateBinary();

        return response($binary, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="modelo_funcionarios.xlsx"',
        ]);
    }

    public function store(Request $request)
    {
        return $this->storeMultiple($request);
    }

    public function storeMultiple(Request $request)
    {
        $clientId = (int) $request->attributes->get('client_id');
        $operatorId = (int) ($request->user()?->operador_id ?: 1);

        $validated = $request->validate([
            'funcionarios' => ['required', 'array', 'min:1'],
            'funcionarios.*.name' => ['nullable', 'string', 'max:255'],
            'funcionarios.*.email' => ['nullable', 'string', 'max:255'],
            'funcionarios.*.cargo' => ['nullable', 'string', 'max:100'],
            'funcionarios.*.telefone' => ['nullable', 'string', 'max:50'],
            'funcionarios.*.endereco' => ['nullable', 'string', 'max:255'],
        ]);

        $rows = collect($validated['funcionarios'])->values()->map(function ($row, $idx) {
            return [
                'line' => $idx + 1,
                'name' => trim((string) ($row['name'] ?? '')),
                'email' => mb_strtolower(trim((string) ($row['email'] ?? ''))),
                'cargo' => trim((string) ($row['cargo'] ?? '')),
                'telefone' => trim((string) ($row['telefone'] ?? '')),
                'endereco' => trim((string) ($row['endereco'] ?? '')),
            ];
        })->all();

        [$created, $errors] = $this->persistRows($rows, $clientId, $operatorId);

        return $this->buildBulkRedirectResponse($created, $errors, null);
    }

    public function importCsv(Request $request)
    {
        $clientId = (int) $request->attributes->get('client_id');
        $operatorId = (int) ($request->user()?->operador_id ?: 1);

        $request->validate([
            'arquivo' => ['required', 'file', 'mimes:csv,txt,xlsx', 'max:4096'],
        ]);

        $file = $request->file('arquivo');

        try {
            [$rows, $totalRead] = $this->extractRowsFromFile($file->getRealPath(), strtolower((string) $file->getClientOriginalExtension()));
        } catch (\RuntimeException $e) {
            return back()->withErrors(['arquivo' => $e->getMessage()])->withInput();
        }

        [$created, $errors] = $this->persistRows($rows, $clientId, $operatorId);

        return $this->buildBulkRedirectResponse($created, $errors, $totalRead);
    }

    public function downloadImportErrors(Request $request)
    {
        $bulk = session('bulk_results');
        $errors = $bulk['errors'] ?? [];

        if (empty($errors)) {
            return back()->with('error', 'Não ha relatório de erros para download.');
        }

        $filename = 'importacao-funcionarios-erros-'.now()->format('Ymd_His').'.csv';
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($errors): void {
            $out = fopen('php://output', 'w');
            fwrite($out, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($out, ['erro']);
            foreach ($errors as $error) {
                fputcsv($out, [$error]);
            }
            fclose($out);
        };

        return Response::stream($callback, 200, $headers);
    }

    public function regenerateActivation(User $funcionario, Request $request)
    {
        $clientId = (int) $request->attributes->get('client_id');
        abort_unless((int) $funcionario->client_id === $clientId, 403, 'Acesso não autorizado.');
        abort_unless(in_array((string) $funcionario->role, self::EMPLOYEE_ROLES, true), 403, 'Acao permitida apenas para funcionarios.');

        $token = $this->generateActivationToken();
        $funcionario->update([
            'activation_token' => $token,
            'activation_expires_at' => now()->addHours(48),
            'activated_at' => null,
            'password' => $this->placeholderPassword(),
        ]);

        return back()->with('success', 'Link de ativação regenerado com sucesso.');
    }

    public function sendInvite(User $funcionario, Request $request)
    {
        $clientId = (int) $request->attributes->get('client_id');
        abort_unless((int) $funcionario->client_id === $clientId, 403, 'Acesso não autorizado.');
        abort_unless(in_array((string) $funcionario->role, self::EMPLOYEE_ROLES, true), 403, 'Acao permitida apenas para funcionarios.');

        if (!is_null($funcionario->activated_at)) {
            return back()->with('error', 'Conta ja ativada. Convite não enviado.');
        }

        if (empty($funcionario->activation_token) || now()->greaterThan($funcionario->activation_expires_at)) {
            $funcionario->update([
                'activation_token' => $this->generateActivationToken(),
                'activation_expires_at' => now()->addHours(48),
                'activated_at' => null,
                'password' => $this->placeholderPassword(),
            ]);
            $funcionario->refresh();
        }

        Mail::to($funcionario->email)->send(new FuncionarioActivationInviteMail($funcionario));

        return back()->with('success', "Convite enviado para {$funcionario->email}.");
    }

    public function sendInviteBulk(Request $request)
    {
        $clientId = (int) $request->attributes->get('client_id');
        $ids = collect($request->input('funcionarios', []))
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->values();

        if ($ids->isEmpty()) {
            return back()->with('error', 'Selecione ao menos um funcionario para enviar convite.');
        }

        $sent = 0;
        $ignored = 0;

        $funcionarios = User::query()
            ->where('client_id', $clientId)
            ->whereIn('id', $ids)
            ->whereIn('role', self::EMPLOYEE_ROLES)
            ->get();

        foreach ($funcionarios as $funcionario) {
            if (!is_null($funcionario->activated_at)) {
                $ignored++;
                continue;
            }

            if (empty($funcionario->activation_token) || now()->greaterThan($funcionario->activation_expires_at)) {
                $funcionario->update([
                    'activation_token' => $this->generateActivationToken(),
                    'activation_expires_at' => now()->addHours(48),
                    'activated_at' => null,
                    'password' => $this->placeholderPassword(),
                ]);
                $funcionario->refresh();
            }

            Mail::to($funcionario->email)->send(new FuncionarioActivationInviteMail($funcionario));
            $sent++;
        }

        $msg = "{$sent} convite(s) enviado(s) com sucesso.";
        if ($ignored > 0) {
            $msg .= " {$ignored} conta(s) ja ativada(s) foram ignoradas.";
        }

        return back()->with('success', $msg);
    }

    public function destroyBulk(Request $request)
    {
        $clientId = (int) $request->attributes->get('client_id');
        $authUserId = (int) $request->user()->id;

        $data = $request->validate([
            'funcionarios' => ['required', 'array', 'min:1'],
            'funcionarios.*' => ['required', 'integer'],
            'password' => ['required', 'current_password'],
        ], [
            'password.current_password' => 'Senha incorreta.',
        ]);

        $ids = collect($data['funcionarios'])
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->values();

        if ($ids->isEmpty()) {
            return back()->with('error', 'Selecione ao menos um funcionario para excluir.');
        }

        if ($ids->contains($authUserId)) {
            return back()->with('error', 'Não e permitido excluir seu próprio usuario.');
        }

        $deleted = User::query()
            ->where('client_id', $clientId)
            ->whereIn('id', $ids)
            ->whereIn('role', self::EMPLOYEE_ROLES)
            ->delete();

        if ($deleted === 0) {
            return back()->with('error', 'Nenhum funcionario foi excluído.');
        }

        return back()->with('success', "{$deleted} funcionario(s) excluído(s) com sucesso.");
    }

    public function show(User $funcionario, Request $request)
    {
        $clientId = (int) $request->attributes->get('client_id');
        abort_unless((int) $funcionario->client_id === $clientId, 403, 'Acesso não autorizado.');
        abort_unless(in_array((string) $funcionario->role, self::EMPLOYEE_ROLES, true), 403, 'Registro não disponivel nesta tela.');

        return view('tenant.funcionarios.show', compact('funcionario'));
    }

    private function extractRowsFromFile(string $path, string $ext): array
    {
        if (in_array($ext, ['csv', 'txt'], true)) {
            return $this->extractRowsFromCsv($path);
        }

        if ($ext === 'xlsx') {
            return $this->extractRowsFromXlsx($path);
        }

        throw new \RuntimeException('Formato não suportado. Use CSV ou XLSX.');
    }

    private function extractRowsFromCsv(string $path): array
    {
        $handle = fopen($path, 'r');
        if ($handle === false) {
            throw new \RuntimeException('Não foi possível ler o arquivo CSV.');
        }

        $header = fgetcsv($handle, 0, ',');
        if (!$header) {
            fclose($handle);
            throw new \RuntimeException('CSV vazio ou inválido.');
        }

        $headerMap = $this->resolveHeaderMap($header);
        if (!$headerMap) {
            fclose($handle);
            throw new \RuntimeException('Cabecalho inválido. Use: nome,email,cargo,telefone[,endereco]');
        }

        $rows = [];
        $lineNumber = 1;
        while (($row = fgetcsv($handle, 0, ',')) !== false) {
            $lineNumber++;

            if (count($row) === 1 && str_contains((string) $row[0], ',')) {
                $row = str_getcsv((string) $row[0], ',', '"', '\\');
            }

            if (count($row) === 1 && trim((string) $row[0]) === '') {
                continue;
            }

            $rows[] = [
                'line' => $lineNumber,
                'name' => trim((string) ($row[$headerMap['nome']] ?? '')),
                'email' => mb_strtolower(trim((string) ($row[$headerMap['email']] ?? ''))),
                'cargo' => trim((string) ($row[$headerMap['cargo']] ?? '')),
                'telefone' => trim((string) ($row[$headerMap['telefone']] ?? '')),
                'endereco' => isset($headerMap['endereco']) ? trim((string) ($row[$headerMap['endereco']] ?? '')) : '',
            ];
        }
        fclose($handle);

        return [$rows, count($rows)];
    }

    private function extractRowsFromXlsx(string $path): array
    {
        if (!class_exists(ZipArchive::class)) {
            throw new \RuntimeException('Suporte a XLSX indisponivel no servidor (ZipArchive).');
        }

        $zip = new ZipArchive();
        if ($zip->open($path) !== true) {
            throw new \RuntimeException('Não foi possível abrir o arquivo XLSX.');
        }

        $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
        if ($sheetXml === false) {
            $zip->close();
            throw new \RuntimeException('XLSX inválido: aba sheet1 não encontrada.');
        }

        $sharedStrings = [];
        $sharedXml = $zip->getFromName('xl/sharedStrings.xml');
        if ($sharedXml !== false) {
            $sharedObj = simplexml_load_string($sharedXml);
            if ($sharedObj && isset($sharedObj->si)) {
                foreach ($sharedObj->si as $si) {
                    if (isset($si->t)) {
                        $sharedStrings[] = (string) $si->t;
                    } else {
                        $parts = [];
                        foreach ($si->r as $run) {
                            $parts[] = (string) $run->t;
                        }
                        $sharedStrings[] = implode('', $parts);
                    }
                }
            }
        }

        $sheetObj = simplexml_load_string($sheetXml);
        if (!$sheetObj || !isset($sheetObj->sheetData)) {
            $zip->close();
            throw new \RuntimeException('XLSX inválido: não foi possível ler planilha.');
        }

        $matrix = [];
        foreach ($sheetObj->sheetData->row as $row) {
            $cells = [];
            foreach ($row->c as $cell) {
                $ref = (string) ($cell['r'] ?? '');
                $colIndex = $this->columnIndexFromRef($ref);
                $type = (string) ($cell['t'] ?? '');

                $value = '';
                if ($type === 's') {
                    $idx = (int) ($cell->v ?? 0);
                    $value = (string) ($sharedStrings[$idx] ?? '');
                } elseif ($type === 'inlineStr') {
                    $value = (string) ($cell->is->t ?? '');
                } else {
                    $value = (string) ($cell->v ?? '');
                }

                $cells[$colIndex] = trim($value);
            }

            if (!empty($cells)) {
                ksort($cells);
                $matrix[] = $cells;
            }
        }
        $zip->close();

        if (empty($matrix)) {
            throw new \RuntimeException('XLSX vazio.');
        }

        $header = $this->rowToSequentialArray($matrix[0]);
        $headerMap = $this->resolveHeaderMap($header);
        if (!$headerMap) {
            throw new \RuntimeException('Cabecalho inválido. Use: nome,email,cargo,telefone[,endereco]');
        }

        $rows = [];
        for ($i = 1; $i < count($matrix); $i++) {
            $line = $i + 1;
            $row = $this->rowToSequentialArray($matrix[$i]);

            if (count($row) === 1 && trim((string) $row[0]) === '') {
                continue;
            }

            $rows[] = [
                'line' => $line,
                'name' => trim((string) ($row[$headerMap['nome']] ?? '')),
                'email' => mb_strtolower(trim((string) ($row[$headerMap['email']] ?? ''))),
                'cargo' => trim((string) ($row[$headerMap['cargo']] ?? '')),
                'telefone' => trim((string) ($row[$headerMap['telefone']] ?? '')),
                'endereco' => isset($headerMap['endereco']) ? trim((string) ($row[$headerMap['endereco']] ?? '')) : '',
            ];
        }

        return [$rows, count($rows)];
    }

    private function resolveHeaderMap(array $header): ?array
    {
        $normalized = collect($header)->map(function ($value) {
            $clean = preg_replace('/^\xEF\xBB\xBF/u', '', (string) $value);
            return Str::of($clean)->ascii()->lower()->trim()->value();
        })->values()->all();

        $idxNome = array_search('nome', $normalized, true);
        $idxEmail = array_search('email', $normalized, true);
        $idxCargo = array_search('cargo', $normalized, true);
        $idxTelefone = array_search('telefone', $normalized, true);
        $idxEndereco = array_search('endereco', $normalized, true);

        if ($idxNome === false || $idxEmail === false || $idxCargo === false || $idxTelefone === false) {
            return null;
        }

        return [
            'nome' => $idxNome,
            'email' => $idxEmail,
            'cargo' => $idxCargo,
            'telefone' => $idxTelefone,
            'endereco' => $idxEndereco === false ? null : $idxEndereco,
        ];
    }

    private function persistRows(array $rows, int $clientId, int $operatorId): array
    {
        $created = [];
        $errors = [];
        $seenEmails = [];

        DB::transaction(function () use ($rows, $clientId, $operatorId, &$created, &$errors, &$seenEmails) {
            foreach ($rows as $row) {
                $line = (int) ($row['line'] ?? 0);
                $name = trim((string) ($row['name'] ?? ''));
                $email = mb_strtolower(trim((string) ($row['email'] ?? '')));
                $cargo = trim((string) ($row['cargo'] ?? ''));
                $telefone = trim((string) ($row['telefone'] ?? ''));
                $endereco = trim((string) ($row['endereco'] ?? ''));

                $validator = Validator::make(
                    compact('name', 'email', 'cargo', 'telefone', 'endereco'),
                    [
                        'name' => ['required', 'string', 'max:255'],
                        'email' => ['required', 'email:rfc,dns', 'max:255'],
                        'cargo' => ['nullable', 'string', 'max:100'],
                        'telefone' => ['nullable', 'string', 'max:50'],
                        'endereco' => ['nullable', 'string', 'max:255'],
                    ]
                );

                if ($validator->fails()) {
                    $errors[] = "Linha {$line}: ".$validator->errors()->first();
                    continue;
                }

                if (isset($seenEmails[$email])) {
                    $errors[] = "Linha {$line}: e-mail duplicado no arquivo ({$email}).";
                    continue;
                }
                $seenEmails[$email] = true;

                $existsSameClient = User::query()
                    ->where('client_id', $clientId)
                    ->whereRaw('LOWER(email) = ?', [$email])
                    ->exists();

                if ($existsSameClient) {
                    $errors[] = "Linha {$line}: e-mail ja cadastrado neste cliente ({$email}).";
                    continue;
                }

                $existsGlobal = User::query()
                    ->whereRaw('LOWER(email) = ?', [$email])
                    ->exists();

                if ($existsGlobal) {
                    $errors[] = "Linha {$line}: e-mail ja cadastrado no sistema ({$email}).";
                    continue;
                }

                $token = $this->generateActivationToken();
                $user = User::query()->create([
                    'name' => $name,
                    'email' => $email,
                    'cargo' => $cargo !== '' ? $cargo : null,
                    'telefone' => $telefone !== '' ? $telefone : null,
                    'endereco' => $endereco !== '' ? $endereco : null,
                    'role' => 'CLIENT_USER',
                    'operador_id' => $operatorId,
                    'client_id' => $clientId,
                    'cliente_id' => $clientId,
                    'ativo' => true,
                    'password' => $this->placeholderPassword(),
                    'activation_token' => $token,
                    'activation_expires_at' => now()->addHours(48),
                    'activated_at' => null,
                ]);

                $created[] = [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'status' => $user->activated_at ? 'Ativado' : 'Pendente',
                    'activation_link' => route('activation.show', $token),
                ];
            }
        });

        return [$created, $errors];
    }

    private function buildBulkRedirectResponse(array $created, array $errors, ?int $totalRead)
    {
        $createdCount = count($created);
        $errorCount = count($errors);
        $message = $createdCount > 0
            ? "{$createdCount} funcionario(s) cadastrados com sucesso."
            : 'Nenhum funcionario foi cadastrado.';

        $payload = [
            'created' => $created,
            'errors' => $errors,
            'created_count' => $createdCount,
            'error_count' => $errorCount,
            'message' => $message,
        ];

        if (!is_null($totalRead)) {
            $payload['source'] = 'arquivo';
            $payload['total_read'] = $totalRead;
        }

        if ($createdCount === 0) {
            return back()->withInput()->with('bulk_results', $payload);
        }

        return redirect()
            ->route('tenant.funcionarios.index')
            ->with('success', $message)
            ->with('bulk_results', $payload);
    }

    private function rowToSequentialArray(array $row): array
    {
        if (empty($row)) {
            return [];
        }

        $max = (int) max(array_keys($row));
        $out = [];
        for ($i = 0; $i <= $max; $i++) {
            $out[] = (string) ($row[$i] ?? '');
        }

        return $out;
    }

    private function columnIndexFromRef(string $ref): int
    {
        if ($ref === '') {
            return 0;
        }

        preg_match('/^[A-Z]+/', strtoupper($ref), $m);
        $letters = $m[0] ?? 'A';
        $idx = 0;
        for ($i = 0; $i < strlen($letters); $i++) {
            $idx = $idx * 26 + (ord($letters[$i]) - 64);
        }

        return max(0, $idx - 1);
    }

    private function buildXlsxTemplateBinary(): string
    {
        if (!class_exists(ZipArchive::class)) {
            throw new \RuntimeException('Suporte a XLSX indisponivel no servidor (ZipArchive).');
        }

        $tmp = tempnam(sys_get_temp_dir(), 'mtx');
        if ($tmp === false) {
            throw new \RuntimeException('Falha ao gerar modelo XLSX.');
        }

        $zip = new ZipArchive();
        if ($zip->open($tmp, ZipArchive::OVERWRITE) !== true) {
            @unlink($tmp);
            throw new \RuntimeException('Falha ao gerar modelo XLSX.');
        }

        $zip->addFromString('[Content_Types].xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            .'<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            .'<Default Extension="xml" ContentType="application/xml"/>'
            .'<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
            .'<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
            .'<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>'
            .'</Types>');

        $zip->addFromString('_rels/.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            .'<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
            .'</Relationships>');

        $zip->addFromString('xl/workbook.xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" '
            .'xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            .'<sheets><sheet name="Funcionários" sheetId="1" r:id="rId1"/></sheets>'
            .'</workbook>');

        $zip->addFromString('xl/_rels/workbook.xml.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            .'<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>'
            .'<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>'
            .'</Relationships>');

        $sheet = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            .'<sheetData>'
            .'<row r="1">'
            .'<c r="A1" t="inlineStr"><is><t>nome</t></is></c>'
            .'<c r="B1" t="inlineStr"><is><t>email</t></is></c>'
            .'<c r="C1" t="inlineStr"><is><t>cargo</t></is></c>'
            .'<c r="D1" t="inlineStr"><is><t>telefone</t></is></c>'
            .'<c r="E1" t="inlineStr"><is><t>endereco</t></is></c>'
            .'</row>'
            .'<row r="2">'
            .'<c r="A2" t="inlineStr"><is><t>Carlos Silva</t></is></c>'
            .'<c r="B2" t="inlineStr"><is><t>carlos.silva@cliente.com</t></is></c>'
            .'<c r="C2" t="inlineStr"><is><t>Operador</t></is></c>'
            .'<c r="D2" t="inlineStr"><is><t>11999990001</t></is></c>'
            .'<c r="E2" t="inlineStr"><is><t>Rua Alfa, 123 - Centro, Cajamar - SP</t></is></c>'
            .'</row>'
            .'</sheetData>'
            .'</worksheet>';
        $zip->addFromString('xl/worksheets/sheet1.xml', $sheet);

        $zip->addFromString('xl/styles.xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            .'<fonts count="1"><font><sz val="11"/><name val="Calibri"/></font></fonts>'
            .'<fills count="1"><fill><patternFill patternType="none"/></fill></fills>'
            .'<borders count="1"><border/></borders>'
            .'<cellStyleXfs count="1"><xf/></cellStyleXfs>'
            .'<cellXfs count="1"><xf xfId="0"/></cellXfs>'
            .'</styleSheet>');

        $zip->close();
        $content = file_get_contents($tmp);
        @unlink($tmp);

        if ($content === false) {
            throw new \RuntimeException('Falha ao finalizar modelo XLSX.');
        }

        return $content;
    }

    private function generateActivationToken(): string
    {
        do {
            $token = Str::random(64);
            $exists = User::query()->where('activation_token', $token)->exists();
        } while ($exists);

        return $token;
    }

    private function placeholderPassword(): string
    {
        return Hash::make(Str::random(40));
    }
}


