<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cliente extends Model
{
    protected $table = 'clientes';

    protected $fillable = [
        'operador_id',
        'razao_social',
        'nome_fantasia',
        'cnpj',
        'documento',
        'email',
        'telefone',
        'endereco',
        'whatsapp',
        'cidade',
        'uf',
        'observacoes',
        'ativo',
    ];

    protected $casts = [
        'ativo' => 'boolean',
    ];

    public function usuarios(): HasMany
    {
        return $this->hasMany(User::class, 'cliente_id');
    }

    public function clientUsers(): HasMany
    {
        return $this->hasMany(User::class, 'client_id');
    }

    public function operador(): BelongsTo
    {
        return $this->belongsTo(Operador::class, 'operador_id');
    }

    public function passageiros(): HasMany
    {
        return $this->hasMany(Passageiro::class, 'cliente_id');
    }

    public function solicitacoes(): HasMany
    {
        return $this->hasMany(SolicitacaoViagem::class, 'cliente_id');
    }

    public function viagens(): HasMany
    {
        return $this->hasMany(Viagem::class, 'cliente_id');
    }
}
