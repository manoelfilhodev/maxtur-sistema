<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\SettingsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;


class UserController extends Controller
{
    // ==============================
    // LISTAGEM
    // ==============================
    public function index()
    {
        // Verifica se é admin
        if (session('tipo') !== 'admin') {
            return redirect()->route('dashboard')->with('error', 'Acesso não autorizado!');
        }

        $usuarios = User::where('email', '!=', 'admin@systex.com.br')
            ->orderBy('name')
            ->get();

        return view('usuarios.index', compact('usuarios'));
    }

    // ==============================
    // FORM DE CRIAÇÃO
    // ==============================
    public function create()
{
    $row = DB::table('settings')->where('key', 'ponto.jornadas')->first();
    $jornadas = [];

    if ($row && !empty($row->value)) {
        $decoded = json_decode($row->value, true);
        if (is_array($decoded)) $jornadas = $decoded;
    }

    return view('painel.usuarios.create', compact('jornadas'));
}


    // ==============================
    // SALVAR NOVO USUÁRIO
    // ==============================
    public function store(Request $request)
    {
        $request->validate([
            'name'       => 'required|string|max:255',
            'email'      => 'required|string|email|max:255|unique:users,email',
            'password'   => 'required|string|min:6',
            'cpf'        => 'required|string|max:14|unique:users,cpf',
            'cargo'      => 'nullable|string|max:255',
            'cliente_id' => 'nullable|integer',
            'ativo'      => 'required|boolean',

            // ✅ jornada/turno
            'jornada_id' => 'nullable|string|max:50',
            'turno_id'   => 'nullable|string|max:50',
            
            'ferias_ativo'  => 'required|boolean',
            'ferias_inicio' => 'nullable|date',
            'ferias_fim'    => 'nullable|date|after_or_equal:ferias_inicio',

        ]);

        $turnoId = $request->jornada_id === 'DIARISTA' ? null : $request->turno_id;

        User::create([
            'name'       => $request->name,
            'email'      => $request->email,
            'password'   => Hash::make($request->password),
            'cpf'        => $request->cpf,
            'cargo'      => $request->cargo,
            'cliente_id' => $request->cliente_id,
            'foto'       => $request->foto ?? null,
            'ativo'      => $request->ativo,

            'jornada_id' => $request->jornada_id,
            'turno_id'   => $turnoId,
            'ferias_ativo'  => (int) $request->ferias_ativo,
            'ferias_inicio' => $request->ferias_inicio,
            'ferias_fim'    => $request->ferias_fim,

        ]);

        return redirect()->route('usuarios.index')->with('success', 'Usuário cadastrado com sucesso!');
    }

    // ==============================
    // FORM DE EDIÇÃO
    // ==============================
public function edit($id)
{
    $usuario = User::findOrFail($id);

    $row = DB::table('settings')->where('key', 'ponto.jornadas')->first();
    $jornadas = [];

    if ($row && !empty($row->value)) {
        $decoded = json_decode($row->value, true);
        if (is_array($decoded)) $jornadas = $decoded;
    }

    return view('painel.usuarios.edit', compact('usuario', 'jornadas'));
}

    // ==============================
    // ATUALIZAR USUÁRIO
    // ==============================
    public function update(Request $request, $id)
    {
        $usuario = User::find($id);

        if (!$usuario) {
            return redirect()->route('usuarios.index')->with('error', 'Usuário não encontrado.');
        }

        $request->validate([
            'name'       => 'required|string|max:255',
            'email'      => 'required|string|email|max:255|unique:users,email,' . $id,
            'cpf'        => 'required|string|max:14|unique:users,cpf,' . $id,
            'cargo'      => 'nullable|string|max:255',
            'cliente_id' => 'nullable|integer',
            'ativo'      => 'required|boolean',

            // ✅ jornada/turno
            'jornada_id' => 'nullable|string|max:50',
            'turno_id'   => 'nullable|string|max:50',

            // senha opcional
            'password'   => 'nullable|string|min:6',
            
            'ferias_ativo'  => 'required|boolean',
            'ferias_inicio' => 'nullable|date',
            'ferias_fim'    => 'nullable|date|after_or_equal:ferias_inicio',

        ]);
        
        $turnoId = $request->jornada_id === 'DIARISTA' ? null : $request->turno_id;

        $data = [
            'name'       => $request->name,
            'email'      => $request->email,
            'cpf'        => $request->cpf,
            'cargo'      => $request->cargo,
            'cliente_id' => $request->cliente_id,
            'ativo'      => $request->ativo,

            'jornada_id' => $request->jornada_id,
            'turno_id'   => $turnoId,
            'ferias_ativo'  => (int) $request->ferias_ativo,
            'ferias_inicio' => $request->ferias_inicio,
            'ferias_fim'    => $request->ferias_fim,

        ];

        // Atualiza senha apenas se vier preenchida
        if (!empty($request->password)) {
            $data['password'] = Hash::make($request->password);
        }

        $usuario->update($data);

        return redirect()->route('usuarios.index')->with('success', 'Usuário atualizado com sucesso!');
    }

    // ==============================
    // EXCLUIR USUÁRIO
    // ==============================
    public function destroy($id)
    {
        $usuario = User::find($id);

        if (!$usuario) {
            return redirect()->route('usuarios.index')->with('error', 'Usuário não encontrado.');
        }

        $usuario->delete();

        return redirect()->route('usuarios.index')->with('success', 'Usuário excluído com sucesso!');
    }
}
