<?php

namespace App\Http\Controllers\Painel;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        

        // Usuários ativos (campo ativo = 1)
        $usuariosAtivos = User::count();


        return view('painel.dashboard', compact(
            'usuariosAtivos'
        ));
    }
}
