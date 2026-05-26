<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OcorrenciaViagem extends Model
{
    protected $table = 'ocorrencias_viagem';

    protected $fillable = [
        'operador_id',
        'cliente_id',
        'solicitacao_id',
        'tipo',
        'descricao',
        'evidencia_path',
        'registrado_por',
        'registrado_em',
    ];

    protected $casts = [
        'registrado_em' => 'datetime',
    ];

    public function solicitacao(): BelongsTo
    {
        return $this->belongsTo(SolicitacaoViagem::class, 'solicitacao_id');
    }

    public function responsavel(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registrado_por');
    }
}
