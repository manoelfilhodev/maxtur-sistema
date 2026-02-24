<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChecklistResposta extends Model
{
    protected $table = 'checklist_respostas';

    protected $fillable = [
        'operador_id',
        'checklist_id',
        'checklist_item_id',
        'codigo',
        'status',
        'observacao',
        'foto_path',
    ];

    protected $casts = [
        'codigo' => 'integer',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(ChecklistItem::class, 'checklist_item_id');
    }
    
    public function checklist(): BelongsTo
    {
        return $this->belongsTo(Checklist::class, 'checklist_id');
    }

    public function operador(): BelongsTo
    {
        return $this->belongsTo(Operador::class, 'operador_id');
    }

}
