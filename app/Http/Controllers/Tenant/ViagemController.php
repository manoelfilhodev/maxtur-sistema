<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Viagem;
use Illuminate\Http\Request;

class ViagemController extends Controller
{
    public function index(Request $request)
    {
        $clientId = (int) $request->attributes->get('client_id');

        $query = Viagem::query()
            ->with(['veiculo:id,placa,modelo', 'motorista:id,nome'])
            ->where('cliente_id', $clientId)
            ->orderByDesc('data_prevista');

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        if ($request->filled('data_inicio')) {
            $query->whereDate('data_prevista', '>=', $request->string('data_inicio'));
        }

        if ($request->filled('data_fim')) {
            $query->whereDate('data_prevista', '<=', $request->string('data_fim'));
        }

        $viagens = $query->paginate(20)->withQueryString();

        return view('tenant.viagens.index', compact('viagens'));
    }
}

