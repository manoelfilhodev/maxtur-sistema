<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AtrasoPassageiro extends Model
{
    protected $table = 'atrasos_passageiro';

    protected $fillable = [
        'operador_id',
        'cliente_id',
        'solicitacao_id',
        'passageiro_id',
        'minutos_atraso',
        'motivo',
        'ocorrido_em',
        'registrado_por',
    ];

    protected $casts = [
        'ocorrido_em' => 'datetime',
    ];

    public function solicitacao(): BelongsTo
    {
        return $this->belongsTo(SolicitacaoViagem::class, 'solicitacao_id');
    }

    public function passageiro(): BelongsTo
    {
        return $this->belongsTo(Passageiro::class, 'passageiro_id');
    }

    public function responsavel(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registrado_por');
    }
}
