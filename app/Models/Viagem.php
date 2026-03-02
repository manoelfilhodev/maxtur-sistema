<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Viagem extends Model
{
    protected $table = 'viagens';

    protected $fillable = [
        'operador_id',
        'cliente_id',
        'veiculo_id',
        'motorista_id',
        'origem',
        'destino',
        'data_prevista',
        'data_real',
        'status',
        'observacoes',
    ];

    protected $casts = [
        'data_prevista' => 'datetime',
        'data_real' => 'datetime',
    ];

    public function operador(): BelongsTo
    {
        return $this->belongsTo(Operador::class, 'operador_id');
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function veiculo(): BelongsTo
    {
        return $this->belongsTo(Veiculo::class, 'veiculo_id');
    }

    public function motorista(): BelongsTo
    {
        return $this->belongsTo(Motorista::class, 'motorista_id');
    }
}
