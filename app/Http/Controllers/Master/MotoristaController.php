<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\MotoristaDocumento;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class MotoristaController extends Controller
{
    public function index()
    {
        $motoristas = User::query()
            ->where(function ($query) {
                $query->whereIn('role', ['motorista', 'MOTORISTA'])
                    ->orWhereIn('cargo', ['motorista', 'MOTORISTA']);
            })
            ->orderBy('name')
            ->paginate(20);

        return view('master.motoristas.index', compact('motoristas'));
    }

    public function create()
    {
        return view('master.motoristas.create');
    }

    public function store(Request $request)
    {
        $data = $this->validateMotorista($request);

        $motorista = User::query()->create([
            'operador_id' => $request->user()->operador_id ?: 1,
            'cliente_id' => null,
            'client_id' => null,
            ...collect($data)->except('password')->all(),
            'nivel' => 'MOTORISTA',
            'role' => 'MOTORISTA',
            'cargo' => 'MOTORISTA',
            'ativo' => (bool) $data['ativo'],
            'password' => Hash::make($data['password']),
        ]);

        return redirect()->route('master.motoristas.show', $motorista->id)->with('success', 'Motorista cadastrado com sucesso.');
    }

    private function validateMotorista(Request $request, ?User $motorista = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($motorista?->id)],
            'cpf' => ['nullable', 'string', 'max:30', Rule::unique('users', 'cpf')->ignore($motorista?->id)],
            'telefone' => ['nullable', 'string', 'max:50'],
            'ativo' => ['required', 'boolean'],
            'password' => [$motorista ? 'nullable' : 'required', 'string', 'min:6'],
            'cnh_vencimento' => ['nullable', 'date'],
            'tipo_recebimento' => ['nullable', Rule::in(['salario', 'por_viagem'])],
            'valor_salario' => ['nullable', 'required_if:tipo_recebimento,salario', 'numeric', 'min:0'],
            'valor_por_viagem' => ['nullable', 'required_if:tipo_recebimento,por_viagem', 'numeric', 'min:0'],
            'data_admissao' => ['nullable', 'date'],
        ]);
    }

    public function show(User $motorista)
    {
        $this->ensureMotorista($motorista);

        $motorista->load('documentosMotorista');

        return view('master.motoristas.show', compact('motorista'));
    }

    public function edit(User $motorista)
    {
        $this->ensureMotorista($motorista);

        return view('master.motoristas.edit', compact('motorista'));
    }

    public function update(Request $request, User $motorista)
    {
        $this->ensureMotorista($motorista);

        $data = $this->validateMotorista($request, $motorista);
        $tipoRecebimento = $data['tipo_recebimento'] ?? $motorista->tipo_recebimento;

        $motorista->fill([
            'operador_id' => $motorista->operador_id ?: 1,
            'cliente_id' => null,
            'client_id' => null,
            'name' => $data['name'],
            'email' => $data['email'],
            'cpf' => $data['cpf'] ?? null,
            'telefone' => $data['telefone'] ?? null,
            'role' => 'MOTORISTA',
            'cargo' => 'MOTORISTA',
            'ativo' => (bool) $data['ativo'],
            'cnh_vencimento' => $data['cnh_vencimento'] ?? null,
            'tipo_recebimento' => $tipoRecebimento,
            'valor_salario' => $tipoRecebimento === 'salario' ? ($data['valor_salario'] ?? $motorista->valor_salario) : null,
            'valor_por_viagem' => $tipoRecebimento === 'por_viagem' ? ($data['valor_por_viagem'] ?? $motorista->valor_por_viagem) : null,
            'data_admissao' => $data['data_admissao'] ?? null,
        ]);

        if (! empty($data['password'])) {
            $motorista->password = Hash::make($data['password']);
        }

        $motorista->save();

        return redirect()->route('master.motoristas.show', $motorista->id)->with('success', 'Motorista atualizado com sucesso.');
    }

    public function storeDocumento(Request $request, User $motorista)
    {
        $this->ensureMotorista($motorista);
        $data = $request->validate([
            'tipo' => ['required', Rule::in(['cnh', 'documento_pessoal', 'comprovante_endereco', 'outro'])],
            'documento' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ]);
        $arquivo = $data['documento'];
        $anterior = $motorista->documentosMotorista()->where('tipo', $data['tipo'])->first();
        $caminho = $arquivo->store("motoristas/{$motorista->id}", 'local');

        $documento = $motorista->documentosMotorista()->create([
            'tipo' => $data['tipo'], 'nome_original' => $arquivo->getClientOriginalName(),
            'caminho' => $caminho, 'mime_type' => $arquivo->getMimeType(), 'tamanho' => $arquivo->getSize(),
        ]);
        if ($anterior) {
            Storage::disk('local')->delete($anterior->caminho);
            $anterior->delete();
        }

        return back()->with('success', $anterior ? 'Documento substituído com sucesso.' : 'Documento anexado com sucesso.');
    }

    public function downloadDocumento(User $motorista, MotoristaDocumento $documento)
    {
        $this->ensureDocumentoOwner($motorista, $documento);
        abort_unless(Storage::disk('local')->exists($documento->caminho), 404);

        return Storage::disk('local')->download($documento->caminho, $documento->nome_original);
    }

    public function destroyDocumento(User $motorista, MotoristaDocumento $documento)
    {
        $this->ensureDocumentoOwner($motorista, $documento);
        Storage::disk('local')->delete($documento->caminho);
        $documento->delete();

        return back()->with('success', 'Documento excluído com sucesso.');
    }

    private function ensureDocumentoOwner(User $motorista, MotoristaDocumento $documento): void
    {
        $this->ensureMotorista($motorista);
        abort_unless((int) $documento->motorista_id === (int) $motorista->id, 404);
    }

    private function ensureMotorista(User $motorista): void
    {
        abort_unless($motorista->isMotorista(), 404);
    }
}
