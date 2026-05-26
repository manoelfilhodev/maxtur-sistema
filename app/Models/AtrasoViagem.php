<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AtrasoViagem extends Model
{
    protected $table = 'atrasos_viagem';

    protected $fillable = [
        'operador_id',
        'cliente_id',
        'solicitacao_id',
        'minutos_atraso',
        'motivo',
        'registrado_por',
    ];

    public function solicitacao(): BelongsTo
    {
        return $this->belongsTo(SolicitacaoViagem::class, 'solicitacao_id');
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function responsavel(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registrado_por');
    }
}
