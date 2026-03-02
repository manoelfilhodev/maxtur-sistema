<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Veiculo;
use Illuminate\Http\Request;

class VeiculoController extends Controller
{
    public function index()
    {
        $veiculos = Veiculo::query()->orderBy('placa')->paginate(20);

        return view('master.veiculos.index', compact('veiculos'));
    }

    public function create()
    {
        return view('master.veiculos.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'placa' => ['required', 'string', 'max:15', 'unique:veiculos,placa'],
            'modelo' => ['required', 'string', 'max:120'],
            'capacidade_passageiros' => ['required', 'integer', 'min:1'],
            'status_operacional' => ['required', 'in:liberado,bloqueado'],
        ]);

        Veiculo::query()->create([
            'operador_id' => 1,
            ...$data,
        ]);

        return redirect()->route('master.veiculos.index')->with('success', 'Veiculo cadastrado com sucesso.');
    }

    public function show(Veiculo $veiculo)
    {
        return view('master.veiculos.show', compact('veiculo'));
    }
}

