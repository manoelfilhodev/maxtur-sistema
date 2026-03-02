<?php

namespace App\Http\Controllers\Master;

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
        $clientes = Cliente::query()
            ->orderBy('razao_social')
            ->paginate(20);

        return view('master.clientes.index', compact('clientes'));
    }

    public function create()
    {
        return view('master.clientes.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'razao_social' => ['required', 'string', 'max:255'],
            'nome_fantasia' => ['nullable', 'string', 'max:255'],
            'cnpj' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'telefone' => ['nullable', 'string', 'max:50'],
            'cidade' => ['nullable', 'string', 'max:120'],
            'uf' => ['nullable', 'string', 'size:2'],
            'email_admin' => ['required', 'email', 'max:255', 'unique:users,email'],
            'nome_admin' => ['nullable', 'string', 'max:255'],
        ]);

        $cliente = null;
        $activationLink = null;

        DB::transaction(function () use ($data, &$cliente, &$activationLink) {
            $cliente = Cliente::query()->create([
                'operador_id' => 1,
                'razao_social' => $data['razao_social'],
                'nome_fantasia' => $data['nome_fantasia'] ?? null,
                'cnpj' => $data['cnpj'] ?? null,
                'email' => $data['email'] ?? null,
                'telefone' => $data['telefone'] ?? null,
                'cidade' => $data['cidade'] ?? null,
                'uf' => $data['uf'] ?? null,
                'ativo' => true,
            ]);

            $token = Str::random(64);
            $user = User::query()->create([
                'name' => $data['nome_admin'] ?? ($cliente->nome_fantasia ?: $cliente->razao_social).' Admin',
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

            $activationLink = route('activation.show', ['token' => $user->activation_token]);
        });

        return redirect()
            ->route('master.clientes.show', $cliente->id)
            ->with('success', 'Conta do cliente criada. Envie este link para ativacao.')
            ->with('ativacao_link', [
                'email' => $data['email_admin'],
                'link' => $activationLink,
            ]);
    }

    public function show(Cliente $cliente)
    {
        $cliente->loadCount(['clientUsers', 'viagens'])
            ->load(['clientUsers' => function ($q) {
                $q->whereIn('role', ['CLIENT_ADMIN', 'CLIENT_USER'])->orderBy('id');
            }]);

        return view('master.clientes.show', compact('cliente'));
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
}
