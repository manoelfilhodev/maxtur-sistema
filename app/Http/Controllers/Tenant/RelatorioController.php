<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Viagem;
use Illuminate\Http\Request;

class RelatorioController extends Controller
{
    public function index(Request $request)
    {
        $clientId = (int) $request->attributes->get('client_id');

        $baseQuery = Viagem::query()->where('cliente_id', $clientId);

        if ($request->filled('status')) {
            $baseQuery->where('status', $request->string('status'));
        }

        if ($request->filled('data_inicio')) {
            $baseQuery->whereDate('data_prevista', '>=', $request->string('data_inicio'));
        }

        if ($request->filled('data_fim')) {
            $baseQuery->whereDate('data_prevista', '<=', $request->string('data_fim'));
        }

        $viagens = (clone $baseQuery)
            ->with(['veiculo:id,placa,modelo', 'motorista:id,nome'])
            ->orderByDesc('data_prevista')
            ->paginate(20)
            ->withQueryString();

        $statusResumo = (clone $baseQuery)
            ->selectRaw('status, COUNT(*) total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $pontuais = (clone $baseQuery)
            ->whereNotNull('data_real')
            ->whereColumn('data_real', '<=', 'data_prevista')
            ->count();

        $atrasadas = (clone $baseQuery)
            ->whereNotNull('data_real')
            ->whereColumn('data_real', '>', 'data_prevista')
            ->count();

        $veiculosUsados = (clone $baseQuery)
            ->selectRaw('veiculo_id, COUNT(*) total')
            ->groupBy('veiculo_id')
            ->with('veiculo:id,placa,modelo')
            ->get();

        $motoristasUsados = (clone $baseQuery)
            ->selectRaw('motorista_id, COUNT(*) total')
            ->groupBy('motorista_id')
            ->with('motorista:id,nome')
            ->get();

        return view('tenant.relatorios.index', compact(
            'viagens',
            'statusResumo',
            'pontuais',
            'atrasadas',
            'veiculosUsados',
            'motoristasUsados'
        ));
    }
}

