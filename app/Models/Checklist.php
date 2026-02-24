<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Checklist extends Model
{
    protected $fillable = [
        'veiculo_identificacao',
        'modelo_veiculo',
        'placa',
        'data',
        'motorista_nome',
        'empresa_fornecedora',
        'inspecionado_por',
        'responsavel_nome',
        'responsavel_funcao',
        'comentarios_motorista',
        'status',
        'created_by',
    ];

    protected $casts = [
        'data' => 'date',
    ];

    public function respostas(): HasMany
    {
        return $this->hasMany(ChecklistResposta::class, 'checklist_id');
    }

    public function criadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
