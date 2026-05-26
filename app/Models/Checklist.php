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
        'operador_id',
        'solicitacao_id',
        'veiculo_id',
        'motorista_id',
        'data',
        'motorista_nome',
        'empresa_fornecedora',
        'inspecionado_por',
        'responsavel_nome',
        'responsavel_funcao',
        'comentarios_motorista',
        'status',
        'resultado',
        'started_at',
        'finished_at',
        'created_by',
    ];

    protected $casts = [
        'data' => 'date',
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

    public function respostas(): HasMany
    {
        return $this->hasMany(ChecklistResposta::class, 'checklist_id');
    }

    public function criadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function operador(): BelongsTo
    {
        return $this->belongsTo(Operador::class, 'operador_id');
    }

    public function solicitacao(): BelongsTo
    {
        return $this->belongsTo(SolicitacaoViagem::class, 'solicitacao_id');
    }

    public function veiculo(): BelongsTo
    {
        return $this->belongsTo(Veiculo::class, 'veiculo_id');
    }

    public function motorista(): BelongsTo
    {
        return $this->belongsTo(User::class, 'motorista_id');
    }
}
