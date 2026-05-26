<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
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
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'cpf' => ['nullable', 'string', 'max:30', 'unique:users,cpf'],
            'telefone' => ['nullable', 'string', 'max:50'],
            'ativo' => ['required', 'boolean'],
            'password' => ['required', 'string', 'min:6'],
        ]);

        $motorista = User::query()->create([
            'operador_id' => 1,
            'cliente_id' => null,
            'client_id' => null,
            'name' => $data['name'],
            'email' => $data['email'],
            'cpf' => $data['cpf'] ?? null,
            'telefone' => $data['telefone'] ?? null,
            'role' => 'MOTORISTA',
            'cargo' => 'MOTORISTA',
            'ativo' => (bool) $data['ativo'],
            'password' => Hash::make($data['password']),
        ]);

        return redirect()->route('master.motoristas.show', $motorista->id)->with('success', 'Motorista cadastrado com sucesso.');
    }

    public function show(User $motorista)
    {
        $this->ensureMotorista($motorista);

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

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($motorista->id)],
            'cpf' => ['nullable', 'string', 'max:30', Rule::unique('users', 'cpf')->ignore($motorista->id)],
            'telefone' => ['nullable', 'string', 'max:50'],
            'ativo' => ['required', 'boolean'],
            'password' => ['nullable', 'string', 'min:6'],
        ]);

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
        ]);

        if (!empty($data['password'])) {
            $motorista->password = Hash::make($data['password']);
        }

        $motorista->save();

        return redirect()->route('master.motoristas.show', $motorista->id)->with('success', 'Motorista atualizado com sucesso.');
    }

    private function ensureMotorista(User $motorista): void
    {
        abort_unless($motorista->isMotorista(), 404);
    }
}
