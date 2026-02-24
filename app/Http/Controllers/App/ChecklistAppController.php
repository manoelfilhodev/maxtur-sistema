<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Checklist;
use App\Models\ChecklistItem;
use App\Models\ChecklistResposta;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ChecklistAppController extends Controller
{
    public function start()
    {
        return view('app.checklist.start');
    }

    public function create(Request $request)
    {
        $request->validate([
            'empresa_fornecedora'   => ['nullable','string','max:120'],
            'modelo_veiculo'        => ['nullable','string','max:120'],
            'placa'                 => ['required','string','max:15'],
            'motorista_nome'        => ['required','string','max:120'],
            'inspecionado_por'      => ['nullable','string','max:120'],
        ]);

        $checklist = Checklist::create([
            'empresa_fornecedora' => $request->empresa_fornecedora,
            'modelo_veiculo'      => $request->modelo_veiculo,
            'placa'               => strtoupper(trim($request->placa)),
            'motorista_nome'      => $request->motorista_nome,
            'inspecionado_por'    => $request->inspecionado_por,
            'data'                => now()->toDateString(),
            'status'              => 'pendente',
        ]);

        $primeiro = ChecklistItem::where('ativo', 1)->orderBy('codigo')->firstOrFail();
        return redirect()->route('app.checklist.step', [$checklist->id, $primeiro->codigo]);
    }

    public function step(Checklist $checklist, int $codigo)
    {
        $item = ChecklistItem::where('ativo', 1)->where('codigo', $codigo)->firstOrFail();

        $resp = ChecklistResposta::where('checklist_id', $checklist->id)
            ->where('checklist_item_id', $item->id)
            ->first();

        $total = ChecklistItem::where('ativo', 1)->count();
        $pos = ChecklistItem::where('ativo', 1)->where('codigo', '<=', $codigo)->count();

        return view('app.checklist.step', compact('checklist','item','resp','total','pos'));
    }

    public function saveStep(Request $request, Checklist $checklist, int $codigo)
    {
        $item = ChecklistItem::where('ativo', 1)->where('codigo', $codigo)->firstOrFail();

        $request->validate([
            'status'     => ['required','in:ok,falha'],
            'observacao' => ['nullable','string','max:500'],
            'foto'       => ['nullable','image','max:4096'], // 4MB
        ]);

        // foto obrigatória se falha
        if ($request->status === 'falha' && !$request->hasFile('foto')) {
            return back()->with('error', 'Para FALHA, a foto é obrigatória.')->withInput();
        }

        $fotoPath = null;
        if ($request->hasFile('foto')) {
            $dir = 'uploads/checklists/'. $checklist->id;
            $name = 'item_'.$item->codigo.'_'.Str::random(10).'.'.$request->file('foto')->getClientOriginalExtension();
            $request->file('foto')->move(public_path($dir), $name);
            $fotoPath = $dir.'/'.$name; // salva como caminho público
        }

        ChecklistResposta::updateOrCreate(
            [
                'checklist_id' => $checklist->id,
                'checklist_item_id' => $item->id,
            ],
            [
                'status' => $request->status,
                'observacao' => $request->observacao,
                'foto_path' => $fotoPath,
            ]
        );

        // próximo item
        $prox = ChecklistItem::where('ativo', 1)->where('codigo', '>', $codigo)->orderBy('codigo')->first();

        if ($prox) {
            return redirect()->route('app.checklist.step', [$checklist->id, $prox->codigo]);
        }

        // finalizar status geral
        $temFalha = ChecklistResposta::where('checklist_id', $checklist->id)->where('status', 'falha')->exists();
        $checklist->status = $temFalha ? 'reprovado' : 'aprovado';
        $checklist->save();

        return redirect()->route('app.checklist.finish', $checklist->id);
    }

    public function finish(Checklist $checklist)
    {
        return view('app.checklist.finish', compact('checklist'));
    }
}
