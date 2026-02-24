<?php

namespace App\Http\Controllers\Painel\Operador;

use App\Http\Controllers\Controller;
use App\Models\Checklist;
use App\Services\TenantContext;
use Illuminate\Http\Request;

class ChecklistController extends Controller
{
    public function __construct(private TenantContext $tenantContext) {}

    public function index(Request $request)
    {
        $checklists = Checklist::query()
            ->with(['veiculo:id,placa,modelo', 'motorista:id,name'])
            ->where('operador_id', $this->tenantContext->operadorId($request->user()))
            ->latest('id')
            ->paginate(20);

        return view('painel.operador.checklists.index', compact('checklists'));
    }

    public function show(Request $request, int $id)
    {
        $checklist = Checklist::query()
            ->with(['veiculo:id,placa,modelo', 'motorista:id,name', 'respostas.item:id,codigo,titulo'])
            ->where('operador_id', $this->tenantContext->operadorId($request->user()))
            ->findOrFail($id);

        return view('painel.operador.checklists.show', compact('checklist'));
    }
}

