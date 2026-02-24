<?php

namespace App\Services\Checklist;

use App\Models\Checklist;
use App\Models\ChecklistItem;
use App\Models\ChecklistResposta;

class ChecklistStatusService
{
    public function recompute(int $checklistId): Checklist
    {
        $checklist = Checklist::findOrFail($checklistId);

        // Catálogo global de itens ativos
        $totalItens = ChecklistItem::query()->where('ativo', 1)->count();

        // total de respostas salvas nessa execução
        $respondidos = ChecklistResposta::where('checklist_id', $checklist->id)->count();

        $falhas = ChecklistResposta::where('checklist_id', $checklist->id)
            ->where('status', 'falha')
            ->count();

        if ($respondidos < $totalItens) {
            $checklist->status = 'pendente';
        } elseif ($falhas > 0) {
            $checklist->status = 'reprovado';
        } else {
            $checklist->status = 'aprovado';
        }

        $checklist->save();

        return $checklist;
    }
}
