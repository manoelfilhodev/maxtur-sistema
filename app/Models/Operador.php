<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Operador extends Model
{
    protected $table = 'operadores';

    protected $fillable = [
        'nome',
        'cnpj',
        'ativo',
    ];

    protected $casts = [
        'ativo' => 'boolean',
    ];

    public function clientes(): HasMany
    {
        return $this->hasMany(Cliente::class, 'operador_id');
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'operador_id');
    }

    public function veiculos(): HasMany
    {
        return $this->hasMany(Veiculo::class, 'operador_id');
    }
}
