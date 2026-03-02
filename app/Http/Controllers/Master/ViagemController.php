<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Models\Motorista;
use App\Models\Veiculo;
use App\Models\Viagem;
use Illuminate\Http\Request;

class ViagemController extends Controller
{
    public function index(Request $request)
    {
        $query = Viagem::query()
            ->with(['cliente:id,nome_fantasia,razao_social', 'veiculo:id,placa,modelo', 'motorista:id,nome'])
            ->orderByDesc('data_prevista');

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        $viagens = $query->paginate(20)->withQueryString();

        return view('master.viagens.index', compact('viagens'));
    }

    public function create()
    {
        $clientes = Cliente::query()->where('ativo', true)->orderBy('razao_social')->get(['id', 'razao_social', 'nome_fantasia']);
        $veiculos = Veiculo::query()->orderBy('placa')->get(['id', 'placa', 'modelo']);
        $motoristas = Motorista::query()->where('ativo', true)->orderBy('nome')->get(['id', 'nome']);

        return view('master.viagens.create', compact('clientes', 'veiculos', 'motoristas'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'cliente_id' => ['required', 'integer', 'exists:clientes,id'],
            'veiculo_id' => ['required', 'integer', 'exists:veiculos,id'],
            'motorista_id' => ['required', 'integer', 'exists:motoristas,id'],
            'origem' => ['required', 'string', 'max:255'],
            'destino' => ['required', 'string', 'max:255'],
            'data_prevista' => ['required', 'date'],
            'data_real' => ['nullable', 'date'],
            'status' => ['required', 'in:programada,em_andamento,realizada,cancelada,atrasada'],
            'observacoes' => ['nullable', 'string'],
        ]);

        $viagem = Viagem::query()->create([
            'operador_id' => 1,
            ...$data,
        ]);

        return redirect()->route('master.viagens.show', $viagem->id)->with('success', 'Viagem criada com sucesso.');
    }

    public function show(Viagem $viagem)
    {
        $viagem->load(['cliente:id,nome_fantasia,razao_social', 'veiculo:id,placa,modelo', 'motorista:id,nome']);

        return view('master.viagens.show', compact('viagem'));
    }
}

