<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Veiculo extends Model
{
    protected $table = 'veiculos';

    protected $fillable = [
        'operador_id',
        'placa',
        'modelo',
        'tipo',
        'ano',
        'data_documento',
        'km_atual',
        'capacidade_passageiros',
        'status_operacional',
    ];

    protected $casts = ['data_documento' => 'date'];

    public function operador(): BelongsTo
    {
        return $this->belongsTo(Operador::class, 'operador_id');
    }

    public function checklists(): HasMany
    {
        return $this->hasMany(Checklist::class, 'veiculo_id');
    }

    public function manutencoes(): HasMany
    {
        return $this->hasMany(VeiculoManutencao::class)->latest();
    }
}
