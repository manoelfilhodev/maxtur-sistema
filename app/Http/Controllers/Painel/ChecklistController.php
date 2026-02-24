<?php

namespace App\Http\Controllers\Painel;

use App\Http\Controllers\Controller;
use App\Models\Checklist;
use App\Models\ChecklistItem;
use App\Models\ChecklistResposta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ChecklistController extends Controller
{
    public function index()
    {
        $checklists = Checklist::orderByDesc('id')->paginate(15);
        return view('painel.checklists.index', compact('checklists'));
    }

    public function create()
    {
        $itens = ChecklistItem::ativos()->get();
        return view('painel.checklists.create', compact('itens'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'veiculo_identificacao' => ['nullable','string','max:50'],
            'data' => ['required','date'],
            'motorista_nome' => ['nullable','string','max:120'],
            'empresa_fornecedora' => ['nullable','string','max:120'],
            'inspecionado_por' => ['nullable','string','max:120'],
            'responsavel_nome' => ['nullable','string','max:120'],
            'responsavel_funcao' => ['nullable','string','max:120'],
            'comentarios_motorista' => ['nullable','string'],
            'itens' => ['required','array'],
        ], [
            'itens.required' => 'Você precisa preencher os itens do checklist.'
        ]);

        $itens = ChecklistItem::ativos()->get()->keyBy('id');

        // Validação: cada item precisa ter status ok/falha
        foreach ($request->input('itens') as $itemId => $row) {
            if (!isset($itens[$itemId])) continue;

            $status = $row['status'] ?? null;
            if (!in_array($status, ['ok','falha'], true)) {
                return back()->withInput()->with('error', 'Preencha todos os itens com OK ou FALHA.');
            }

            // Se falha: foto obrigatória
            if ($status === 'falha') {
                $fotoKey = "itens.$itemId.foto";
                if (!$request->hasFile($fotoKey)) {
                    return back()->withInput()->with('error', "Item #{$itens[$itemId]->codigo} ({$itens[$itemId]->titulo}) está como FALHA e precisa de FOTO.");
                }
            }
        }

        $checklist = Checklist::create([
            'veiculo_identificacao' => $request->veiculo_identificacao,
            'data' => $request->data,
            'motorista_nome' => $request->motorista_nome,
            'empresa_fornecedora' => $request->empresa_fornecedora,
            'inspecionado_por' => $request->inspecionado_por,
            'responsavel_nome' => $request->responsavel_nome,
            'responsavel_funcao' => $request->responsavel_funcao,
            'comentarios_motorista' => $request->comentarios_motorista,
            'status' => 'pendente',
            'created_by' => Auth::id(),
        ]);

        $teveFalha = false;

        foreach ($request->input('itens') as $itemId => $row) {
            if (!isset($itens[$itemId])) continue;

            $status = $row['status'];
            $obs = $row['observacao'] ?? null;

            $fotoPath = null;
            $fotoKey = "itens.$itemId.foto";

            if ($status === 'falha') {
                $teveFalha = true;

                $file = $request->file($fotoKey);
                if ($file) {
                    $ext = strtolower($file->getClientOriginalExtension() ?: 'jpg');
                    $name = 'chk_' . $checklist->id . '_item_' . $itemId . '_' . Str::random(10) . '.' . $ext;

                    $dest = public_path('uploads/checklists');
                    if (!is_dir($dest)) {
                        @mkdir($dest, 0755, true);
                    }

                    $file->move($dest, $name);
                    $fotoPath = 'uploads/checklists/' . $name; // path público
                }
            }

            ChecklistResposta::create([
                'checklist_id' => $checklist->id,
                'checklist_item_id' => (int)$itemId,
                'status' => $status,
                'observacao' => $obs,
                'foto_path' => $fotoPath,
            ]);
        }

        $checklist->status = $teveFalha ? 'reprovado' : 'aprovado';
        $checklist->save();

        return redirect()->route('checklists.show', $checklist->id)
            ->with('success', 'Checklist salvo com sucesso!');
    }

    public function show(Checklist $checklist)
    {
        $respostas = $checklist->respostas()->with('item')->get()->sortBy(fn($r) => $r->item->codigo);
        return view('painel.checklists.show', compact('checklist','respostas'));
    }
}
