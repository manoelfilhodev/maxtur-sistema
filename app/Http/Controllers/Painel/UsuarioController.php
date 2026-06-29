<?php

namespace App\Http\Controllers\Painel;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UsuarioController extends Controller
{
    // ============================================
    // Helper: define se é "Master" (usuário id=1)
    // ============================================
    private function isMaster(): bool
    {
        return Auth::check() && Auth::id() == 1;
    }

    // ============================================
    // LISTAR USUÁRIOS
    // ============================================
    public function index()
    {
        $query = User::query()->where('id', '!=', 1);

        // Se não for master, filtra pelo cliente do usuário logado
        if (! $this->isMaster()) {
            $clienteId = Auth::user()->cliente_id;

            if (! $clienteId) {
                abort(403, 'Seu usuário não está vinculado a um cliente.');
            }

            $query->where('cliente_id', $clienteId);
        }

        $usuarios = $query->orderBy('name')->get();

        return view('painel.usuarios.index', compact('usuarios'));
    }

    // ============================================
    // FORMULÁRIO DE CRIAÇÃO
    // ============================================
    public function create()
    {
        $jornadas = $this->carregarJornadas();

        // Master pode escolher cliente; não-master usa fixo no próprio
        $clientes = DB::table('clientes')
            ->select('id', DB::raw('nome_fantasia as nome'))
            ->orderBy('nome_fantasia')
            ->get();

        $clienteFixo = ! $this->isMaster() ? Auth::user()->cliente_id : null;

        if (! $this->isMaster() && ! $clienteFixo) {
            abort(403, 'Seu usuário não está vinculado a um cliente.');
        }

        return view('painel.usuarios.create', compact('jornadas', 'clientes', 'clienteFixo'));
    }

    // ============================================
    // SALVAR NOVO USUÁRIO
    // ============================================
    public function store(Request $request)
    {
        $request->validate(
            [
                'name' => 'required|string|min:3',
                'email' => 'required|email|unique:users,email',
                'cpf' => 'required|string|min:11|max:14|unique:users,cpf',
                'password' => 'required|string|min:6',
                'nivel' => 'required|in:ADMIN,CLIENTE,MOTORISTA,GESTOR,USUARIO',

                // extras
                'ativo' => 'nullable|boolean',
                'cliente_id' => 'nullable|exists:clientes,id',

                'jornada_id' => 'nullable|string|max:50',
                'turno_id' => 'nullable|string|max:50',

                'ferias_ativo' => 'nullable|boolean',
                'ferias_inicio' => 'nullable|date',
                'ferias_fim' => 'nullable|date|after_or_equal:ferias_inicio',
            ],
            [
                'name.required' => 'O nome completo é obrigatório.',
                'name.min' => 'O nome deve ter pelo menos 3 caracteres.',

                'email.required' => 'O e-mail é obrigatório.',
                'email.email' => 'Informe um e-mail válido.',
                'email.unique' => 'Este e-mail já está cadastrado.',

                'cpf.required' => 'O CPF é obrigatório.',
                'cpf.min' => 'O CPF deve ter ao menos 11 caracteres.',
                'cpf.unique' => 'Este CPF já está cadastrado.',

                'password.required' => 'A senha é obrigatória.',
                'password.min' => 'A senha deve ter pelo menos 6 caracteres.',

                'nivel.required' => 'Selecione um nível de acesso.',

                'cliente_id.exists' => 'Cliente inválido.',
            ]
        );

        // ✅ DIARISTA não tem turno
        $turnoId = ($request->jornada_id === 'DIARISTA') ? null : $request->turno_id;

        // ✅ Regra do cliente:
        // Master escolhe; não-master força o próprio cliente
        $clienteId = $this->isMaster()
            ? $request->cliente_id
            : Auth::user()->cliente_id;

        if (! $clienteId && in_array($request->nivel, ['CLIENTE', 'USUARIO'], true)) {
            return back()->withErrors(['cliente_id' => 'Defina um cliente para este usuário.'])->withInput();
        }

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'cpf' => $request->cpf,
            'cargo' => $request->nivel,
            'nivel' => $request->nivel,
            'role' => $this->roleParaNivel($request->nivel),
            'password' => Hash::make($request->password),

            'ativo' => $request->has('ativo') ? (int) $request->ativo : 1,
            'cliente_id' => $clienteId,

            'jornada_id' => $request->jornada_id,
            'turno_id' => $turnoId,

            'ferias_ativo' => $request->has('ferias_ativo') ? (int) $request->ferias_ativo : 0,
            'ferias_inicio' => $request->ferias_inicio,
            'ferias_fim' => $request->ferias_fim,
        ]);

        return redirect()->route('usuarios.index')->with('success', 'Usuário cadastrado com sucesso!');
    }

    // ============================================
    // EDITAR USUÁRIO
    // ============================================
    public function edit($id)
    {
        if ($id == 1) {
            abort(403, 'Usuário protegido.');
        }

        $usuario = User::findOrFail($id);

        // Se não for master, impede editar usuários de outro cliente
        if (! $this->isMaster()) {
            $clienteId = Auth::user()->cliente_id;

            if (! $clienteId) {
                abort(403, 'Seu usuário não está vinculado a um cliente.');
            }

            if ((int) $usuario->cliente_id !== (int) $clienteId) {
                abort(403, 'Você não tem permissão para editar este usuário.');
            }
        }

        $jornadas = $this->carregarJornadas();

        $clientes = DB::table('clientes')
            ->select('id', DB::raw('nome_fantasia as nome'))
            ->orderBy('nome_fantasia')
            ->get();

        $clienteFixo = ! $this->isMaster() ? Auth::user()->cliente_id : null;

        return view('painel.usuarios.edit', compact('usuario', 'jornadas', 'clientes', 'clienteFixo'));
    }

    // ============================================
    // ATUALIZAR USUÁRIO
    // ============================================
    public function update(Request $request, $id)
    {
        if ($id == 1) {
            abort(403, 'Usuário protegido.');
        }

        $usuario = User::findOrFail($id);

        // Se não for master, impede editar usuários de outro cliente
        if (! $this->isMaster()) {
            $clienteId = Auth::user()->cliente_id;

            if (! $clienteId) {
                abort(403, 'Seu usuário não está vinculado a um cliente.');
            }

            if ((int) $usuario->cliente_id !== (int) $clienteId) {
                abort(403, 'Você não tem permissão para editar este usuário.');
            }
        }

        $request->validate(
            [
                'name' => 'required|string|min:3',
                'email' => 'required|email|unique:users,email,'.$id,
                'cpf' => 'required|string|min:11|max:14|unique:users,cpf,'.$id,
                'nivel' => 'required|in:ADMIN,CLIENTE,MOTORISTA,GESTOR,USUARIO',

                'ativo' => 'nullable|boolean',
                'cliente_id' => 'nullable|exists:clientes,id',

                'jornada_id' => 'nullable|string|max:50',
                'turno_id' => 'nullable|string|max:50',

                'ferias_ativo' => 'nullable|boolean',
                'ferias_inicio' => 'nullable|date',
                'ferias_fim' => 'nullable|date|after_or_equal:ferias_inicio',

                'password' => 'nullable|string|min:6',
            ],
            [
                'name.required' => 'O nome é obrigatório.',
                'email.required' => 'O e-mail é obrigatório.',
                'email.email' => 'Informe um e-mail válido.',
                'email.unique' => 'Este e-mail já está em uso.',

                'cpf.required' => 'O CPF é obrigatório.',
                'cpf.unique' => 'Este CPF já está cadastrado.',

                'nivel.required' => 'Selecione um nível de acesso.',
                'cliente_id.exists' => 'Cliente inválido.',
            ]
        );

        // ✅ DIARISTA não tem turno
        $turnoId = ($request->jornada_id === 'DIARISTA') ? null : $request->turno_id;

        // ✅ Regra do cliente:
        // Master pode trocar; não-master mantém fixo no próprio cliente
        $clienteId = $this->isMaster()
            ? ($request->cliente_id ?? $usuario->cliente_id)
            : Auth::user()->cliente_id;

        if (! $clienteId && in_array($request->nivel, ['CLIENTE', 'USUARIO'], true)) {
            return back()->withErrors(['cliente_id' => 'Defina um cliente para este usuário.'])->withInput();
        }

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'cpf' => $request->cpf,
            'cargo' => $request->nivel,
            'nivel' => $request->nivel,
            'role' => $this->roleParaNivel($request->nivel),

            'ativo' => $request->has('ativo') ? (int) $request->ativo : $usuario->ativo,
            'cliente_id' => $clienteId,

            'jornada_id' => $request->jornada_id,
            'turno_id' => $turnoId,

            'ferias_ativo' => $request->has('ferias_ativo') ? (int) $request->ferias_ativo : $usuario->ferias_ativo,
            'ferias_inicio' => $request->ferias_inicio,
            'ferias_fim' => $request->ferias_fim,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $usuario->update($data);

        return redirect()->route('usuarios.index')->with('success', 'Usuário atualizado com sucesso!');
    }

    // ============================================
    // EXCLUIR USUÁRIO INDIVIDUAL
    // ============================================
    public function destroy($id)
    {
        if ($id == 1) {
            abort(403, 'Usuário protegido.');
        }

        $usuario = User::findOrFail($id);

        // Se não for master, só pode excluir do próprio cliente
        if (! $this->isMaster()) {
            $clienteId = Auth::user()->cliente_id;

            if (! $clienteId) {
                abort(403, 'Seu usuário não está vinculado a um cliente.');
            }

            if ((int) $usuario->cliente_id !== (int) $clienteId) {
                abort(403, 'Você não tem permissão para excluir este usuário.');
            }
        }

        $usuario->delete();

        return redirect()->route('usuarios.index')->with('success', 'Usuário excluído com sucesso!');
    }

    // ============================================
    // EXCLUSÃO EM MASSA
    // ============================================
    public function destroyMultiple(Request $request)
    {
        $ids = json_decode($request->ids, true);

        if (! $ids || ! is_array($ids)) {
            return back()->with('error', 'Nenhum usuário selecionado.');
        }

        if (in_array(Auth::id(), $ids)) {
            return back()->with('error', 'Você não pode excluir a si mesmo.');
        }

        // não remove o user 1
        $ids = array_values(array_filter($ids, fn ($id) => (int) $id !== 1));

        // Se não for master, só pode excluir usuários do próprio cliente
        if (! $this->isMaster()) {
            $clienteId = Auth::user()->cliente_id;

            if (! $clienteId) {
                abort(403, 'Seu usuário não está vinculado a um cliente.');
            }

            User::whereIn('id', $ids)->where('cliente_id', $clienteId)->delete();

            return back()->with('success', 'Usuários selecionados foram removidos com sucesso!');
        }

        User::whereIn('id', $ids)->delete();

        return back()->with('success', 'Usuários selecionados foram removidos com sucesso!');
    }

    // ============================================
    // HELPER: CARREGAR JORNADAS DO SETTINGS
    // ============================================
    private function roleParaNivel(string $nivel): string
    {
        return match ($nivel) {
            'ADMIN' => 'ADMIN',
            'MOTORISTA' => 'MOTORISTA',
            'GESTOR' => 'OPERADOR',
            'CLIENTE' => 'CLIENT_USER',
            default => 'CLIENT_USER',
        };
    }

    private function carregarJornadas(): array
    {
        $row = DB::table('settings')->where('key', 'ponto.jornadas')->first();
        if (! $row || empty($row->value)) {
            return [];
        }

        $decoded = json_decode($row->value, true);

        return is_array($decoded) ? $decoded : [];
    }
}
