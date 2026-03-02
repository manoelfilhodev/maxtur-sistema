<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Motorista;
use Illuminate\Http\Request;

class MotoristaController extends Controller
{
    public function index()
    {
        $motoristas = Motorista::query()->orderBy('nome')->paginate(20);

        return view('master.motoristas.index', compact('motoristas'));
    }

    public function create()
    {
        return view('master.motoristas.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nome' => ['required', 'string', 'max:255'],
            'cnh' => ['nullable', 'string', 'max:30'],
            'telefone' => ['nullable', 'string', 'max:50'],
            'ativo' => ['required', 'boolean'],
        ]);

        Motorista::query()->create([
            'operador_id' => 1,
            ...$data,
        ]);

        return redirect()->route('master.motoristas.index')->with('success', 'Motorista cadastrado com sucesso.');
    }

    public function show(Motorista $motorista)
    {
        return view('master.motoristas.show', compact('motorista'));
    }
}

