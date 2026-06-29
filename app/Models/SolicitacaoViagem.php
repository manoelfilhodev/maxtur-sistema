<?php

namespace App\Models;

use App\Support\ViagemStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class SolicitacaoViagem extends Model
{
    protected $table = 'solicitacoes_viagem';

    protected $fillable = [
        'operador_id',
        'cliente_id',
        'origem',
        'destino',
        'data_hora',
        'iniciada_em',
        'finalizada_em',
        'passageiros_previstos',
        'observacao',
        'status',
        'tipo_periodo',
        'natureza',
    ];

    protected $casts = [
        'data_hora' => 'datetime',
        'iniciada_em' => 'datetime',
        'finalizada_em' => 'datetime',
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

    public function ultimaAtribuicao(): HasOne
    {
        return $this->hasOne(SolicitacaoAtribuicao::class, 'solicitacao_id')->latestOfMany();
    }

    public function checklists(): HasMany
    {
        return $this->hasMany(Checklist::class, 'solicitacao_id');
    }

    public function ultimoChecklist(): HasOne
    {
        return $this->hasOne(Checklist::class, 'solicitacao_id')->latestOfMany();
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
