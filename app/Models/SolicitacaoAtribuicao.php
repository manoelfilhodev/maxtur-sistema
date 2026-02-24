<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SolicitacaoAtribuicao extends Model
{
    protected $table = 'solicitacao_atribuicoes';

    protected $fillable = [
        'operador_id',
        'solicitacao_id',
        'veiculo_id',
        'motorista_id',
        'atribuido_por',
        'atribuido_em',
    ];

    protected $casts = [
        'atribuido_em' => 'datetime',
    ];

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
