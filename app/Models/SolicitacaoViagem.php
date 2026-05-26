<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Support\ViagemStatus;

class SolicitacaoViagem extends Model
{
    protected $table = 'solicitacoes_viagem';

    protected $fillable = [
        'operador_id',
        'cliente_id',
        'origem',
        'destino',
        'data_hora',
        'passageiros_previstos',
        'observacao',
        'status',
    ];

    protected $casts = [
        'data_hora' => 'datetime',
    ];

    public function operador(): BelongsTo
    {
        return $this->belongsTo(Operador::class, 'operador_id');
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function passageiros(): BelongsToMany
    {
        return $this->belongsToMany(Passageiro::class, 'solicitacao_passageiros', 'solicitacao_id', 'passageiro_id')
            ->withTimestamps();
    }

    public function atribuicoes(): HasMany
    {
        return $this->hasMany(SolicitacaoAtribuicao::class, 'solicitacao_id');
    }

    public function ultimaAtribuicao(): HasMany
    {
        return $this->hasMany(SolicitacaoAtribuicao::class, 'solicitacao_id')->latest('atribuido_em')->latest('id');
    }

    public function checklists(): HasMany
    {
        return $this->hasMany(Checklist::class, 'solicitacao_id');
    }

    public function atrasosViagem(): HasMany
    {
        return $this->hasMany(AtrasoViagem::class, 'solicitacao_id');
    }

    public function atrasosPassageiro(): HasMany
    {
        return $this->hasMany(AtrasoPassageiro::class, 'solicitacao_id');
    }

    public function ocorrencias(): HasMany
    {
        return $this->hasMany(OcorrenciaViagem::class, 'solicitacao_id');
    }

    public function statusLabel(): string
    {
        return ViagemStatus::label($this->status);
    }
}
