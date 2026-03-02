<?php

namespace App\Http\Controllers\Painel;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ClienteController extends Controller
{
    public function index(Request $request)
    {
        $query = Cliente::query();

        if ($request->filled('busca')) {
            $busca = trim((string) $request->query('busca'));
            $query->where(function ($q) use ($busca) {
                $q->where('razao_social', 'like', '%'.$busca.'%')
                    ->orWhere('nome_fantasia', 'like', '%'.$busca.'%')
                    ->orWhere('documento', 'like', '%'.$busca.'%')
                    ->orWhere('email', 'like', '%'.$busca.'%');
            });
        }

        if ($request->query('status') === 'ativo') {
            $query->where('ativo', 1);
        } elseif ($request->query('status') === 'inativo') {
            $query->where('ativo', 0);
        }

        $clientes = $query
            ->orderBy('razao_social')
            ->paginate(15)
            ->withQueryString();

        return view('painel.clientes.index', compact('clientes'));
    }

    public function create()
    {
        return view('painel.clientes.create');
    }

    public function store(Request $request)
    {
        $data = $this->validateCliente($request, true);
        $cliente = null;
        $ativacaoLink = null;

        DB::transaction(function () use ($data, &$cliente, &$ativacaoLink) {
            $cliente = Cliente::query()->create([
                'operador_id' => 1,
                'razao_social' => $data['razao_social'],
                'nome_fantasia' => $data['nome_fantasia'] ?? null,
                'cnpj' => $data['cnpj'] ?? null,
                'documento' => $data['documento'] ?? null,
                'email' => $data['email'] ?? null,
                'telefone' => $data['telefone'] ?? null,
                'whatsapp' => $data['whatsapp'] ?? null,
                'cidade' => $data['cidade'] ?? null,
                'uf' => $data['uf'] ?? null,
                'observacoes' => $data['observacoes'] ?? null,
                'ativo' => (bool) ($data['ativo'] ?? true),
            ]);

            $token = Str::random(64);
            $admin = User::query()->create([
                'name' => $data['nome_admin'] ?? (($cliente->nome_fantasia ?: $cliente->razao_social).' Admin'),
                'email' => $data['email_admin'],
                'operador_id' => 1,
                'client_id' => $cliente->id,
                'cliente_id' => $cliente->id,
                'role' => 'CLIENT_ADMIN',
                'ativo' => true,
                'password' => Hash::make(Str::random(48)),
                'activation_token' => $token,
                'activation_expires_at' => now()->addHours(48),
                'activated_at' => null,
            ]);

            $ativacaoLink = route('activation.show', ['token' => $admin->activation_token]);
        });

        return redirect()
            ->route('painel.clientes.show', $cliente->id)
            ->with('success', 'Conta do cliente criada. Envie este link para ativacao:')
            ->with('ativacao_link', [
                'email' => $data['email_admin'],
                'link' => $ativacaoLink,
            ]);
    }

    public function show(Cliente $cliente)
    {
        $cliente->load(['clientUsers' => function ($q) {
            $q->whereIn('role', ['CLIENT_ADMIN', 'CLIENT_USER'])->orderBy('id');
        }]);

        return view('painel.clientes.show', compact('cliente'));
    }

    public function edit(Cliente $cliente)
    {
        return view('painel.clientes.edit', compact('cliente'));
    }

    public function update(Request $request, Cliente $cliente)
    {
        $data = $this->validateCliente($request);
        $cliente->update($data);

        return redirect()->route('painel.clientes.index')->with('success', 'Cliente atualizado com sucesso.');
    }

    public function destroy(Cliente $cliente)
    {
        $cliente->delete();

        return redirect()->route('painel.clientes.index')->with('success', 'Cliente removido com sucesso.');
    }

    public function toggle(Cliente $cliente)
    {
        $cliente->update([
            'ativo' => !((bool) $cliente->ativo),
        ]);

        return redirect()->route('painel.clientes.index')->with('success', 'Status do cliente atualizado.');
    }

    public function regenerateActivation(Cliente $cliente)
    {
        $admin = User::query()
            ->where('client_id', $cliente->id)
            ->where('role', 'CLIENT_ADMIN')
            ->orderBy('id')
            ->first();

        if (!$admin) {
            return back()->with('error', 'CLIENT_ADMIN nao encontrado para este cliente.');
        }

        $admin->update([
            'activation_token' => Str::random(64),
            'activation_expires_at' => now()->addHours(48),
            'activated_at' => null,
            'password' => Hash::make(Str::random(48)),
        ]);

        return back()
            ->with('success', 'Novo link de ativacao gerado com sucesso.')
            ->with('ativacao_link', [
                'email' => $admin->email,
                'link' => route('activation.show', ['token' => $admin->activation_token]),
            ]);
    }

    private function validateCliente(Request $request, bool $creating = false): array
    {
        $rules = [
            'razao_social' => ['required', 'string', 'max:255'],
            'nome_fantasia' => ['nullable', 'string', 'max:255'],
            'cnpj' => ['nullable', 'string', 'max:20'],
            'documento' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'telefone' => ['nullable', 'string', 'max:50'],
            'whatsapp' => ['nullable', 'string', 'max:50'],
            'cidade' => ['nullable', 'string', 'max:120'],
            'uf' => ['nullable', 'string', 'size:2'],
            'ativo' => ['required', 'boolean'],
            'observacoes' => ['nullable', 'string'],
        ];

        if ($creating) {
            $rules['email_admin'] = ['required', 'email', 'max:255', 'unique:users,email'];
            $rules['nome_admin'] = ['nullable', 'string', 'max:255'];
        }

        return $request->validate($rules);
    }
}
