<?php

namespace App\Http\Controllers\Painel;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RelatoriosController extends Controller
{
    public function index()
    {
        return view('painel.relatorios.index');
    }

    public function batidasIndex()
    {
        $usuarios = DB::table('users')
            ->select('id', 'name', 'cpf')
            ->orderBy('name')
            ->get();

        return view('painel.relatorios.batidas.index', compact('usuarios'));
    }

    public function batidasExcel(Request $request)
    {
        return back()->with('error', 'Exportacao Excel indisponivel neste momento.');
    }

    public function batidasPdf(Request $request)
    {
        return back()->with('error', 'Exportacao PDF indisponivel neste momento.');
    }

    public function diarioIndex()
    {
        $usuarios = DB::table('users')
            ->select('id', 'name', 'cpf')
            ->orderBy('name')
            ->get();

        return view('painel.relatorios.diario.index', compact('usuarios'));
    }

    public function diarioExcel(Request $request)
    {
        return back()->with('error', 'Exportacao Excel indisponivel neste momento.');
    }

    public function diarioPdf(Request $request)
    {
        return back()->with('error', 'Exportacao PDF indisponivel neste momento.');
    }
}
