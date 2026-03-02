<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Viagem;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $clientId = (int) $request->attributes->get('client_id');

        $viagensHoje = Viagem::query()
            ->where('cliente_id', $clientId)
            ->whereDate('data_prevista', today())
            ->count();

        $viagensEmAndamento = Viagem::query()
            ->where('cliente_id', $clientId)
            ->where('status', 'em_andamento')
            ->count();

        $viagensProgramadas = Viagem::query()
            ->where('cliente_id', $clientId)
            ->where('status', 'programada')
            ->count();

        $funcionariosAtivos = User::query()
            ->where('client_id', $clientId)
            ->whereIn('role', ['CLIENT_USER', 'funcionario'])
            ->where('ativo', true)
            ->count();

        $funcionariosPendentes = User::query()
            ->where('client_id', $clientId)
            ->whereIn('role', ['CLIENT_USER', 'funcionario'])
            ->whereNull('activated_at')
            ->count();

        $proximasViagens = Viagem::query()
            ->with(['veiculo:id,placa,modelo', 'motorista:id,nome'])
            ->where('cliente_id', $clientId)
            ->where('data_prevista', '>=', now())
            ->orderBy('data_prevista')
            ->limit(8)
            ->get();

        return view('tenant.dashboard', compact(
            'viagensHoje',
            'viagensEmAndamento',
            'viagensProgramadas',
            'funcionariosAtivos',
            'funcionariosPendentes',
            'proximasViagens'
        ));
    }
}

