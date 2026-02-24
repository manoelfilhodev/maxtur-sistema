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
        'capacidade_passageiros',
        'status_operacional',
    ];

    public function operador(): BelongsTo
    {
        return $this->belongsTo(Operador::class, 'operador_id');
    }

    public function checklists(): HasMany
    {
        return $this->hasMany(Checklist::class, 'veiculo_id');
    }
}
