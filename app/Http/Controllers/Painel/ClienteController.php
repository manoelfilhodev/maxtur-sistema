<?php

namespace App\Http\Controllers\Painel;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ClienteController extends Controller
{
    public function index(Request $request)
    {
        $query = DB::table('clientes');

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

        $clientes = $query->orderBy('razao_social')->paginate(15)->withQueryString();

        return view('painel.clientes.index', compact('clientes'));
    }

    public function create()
    {
        return view('painel.clientes.create');
    }

    public function store(Request $request)
    {
        $data = $this->validateCliente($request);
        $now = now();

        DB::table('clientes')->insert(array_merge($data, [
            'created_at' => $now,
            'updated_at' => $now,
        ]));

        return redirect()->route('painel.clientes.index')->with('success', 'Cliente cadastrado com sucesso.');
    }

    public function show($cliente)
    {
        $cliente = DB::table('clientes')->where('id', (int) $cliente)->firstOrFail();

        return view('painel.clientes.show', compact('cliente'));
    }

    public function edit($cliente)
    {
        $cliente = DB::table('clientes')->where('id', (int) $cliente)->firstOrFail();

        return view('painel.clientes.edit', compact('cliente'));
    }

    public function update(Request $request, $cliente)
    {
        $data = $this->validateCliente($request);

        DB::table('clientes')
            ->where('id', (int) $cliente)
            ->update(array_merge($data, ['updated_at' => now()]));

        return redirect()->route('painel.clientes.index')->with('success', 'Cliente atualizado com sucesso.');
    }

    public function destroy($cliente)
    {
        DB::table('clientes')->where('id', (int) $cliente)->delete();

        return redirect()->route('painel.clientes.index')->with('success', 'Cliente removido com sucesso.');
    }

    public function toggle($cliente)
    {
        $row = DB::table('clientes')->where('id', (int) $cliente)->first();

        if (!$row) {
            return redirect()->route('painel.clientes.index')->with('error', 'Cliente nao encontrado.');
        }

        DB::table('clientes')
            ->where('id', (int) $cliente)
            ->update([
                'ativo' => (int) !((int) ($row->ativo ?? 0)),
                'updated_at' => now(),
            ]);

        return redirect()->route('painel.clientes.index')->with('success', 'Status do cliente atualizado.');
    }

    private function validateCliente(Request $request): array
    {
        return $request->validate([
            'razao_social' => ['required', 'string', 'max:255'],
            'nome_fantasia' => ['nullable', 'string', 'max:255'],
            'documento' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'telefone' => ['nullable', 'string', 'max:50'],
            'whatsapp' => ['nullable', 'string', 'max:50'],
            'cidade' => ['nullable', 'string', 'max:120'],
            'uf' => ['nullable', 'string', 'size:2'],
            'ativo' => ['required', 'boolean'],
            'observacoes' => ['nullable', 'string'],
        ]);
    }
}
