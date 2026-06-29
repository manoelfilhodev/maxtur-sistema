<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VeiculoManutencao extends Model
{
    protected $table = 'veiculo_manutencoes';

    protected $fillable = [
        'veiculo_id', 'item', 'km_referencia', 'km_vencimento',
        'data_vencimento', 'observacao', 'status',
    ];

    protected $casts = ['data_vencimento' => 'date'];

    public function veiculo(): BelongsTo
    {
        return $this->belongsTo(Veiculo::class);
    }

    public function calcularStatus(?int $kmAtual = null): string
    {
        $kmAtual ??= (int) $this->veiculo?->km_atual;
        $kmRestante = (int) $this->km_vencimento - $kmAtual;
        $diasRestantes = $this->data_vencimento ? now()->startOfDay()->diffInDays($this->data_vencimento, false) : null;

        if ($kmRestante <= 0 || ($diasRestantes !== null && $diasRestantes < 0)) {
            return 'vencido';
        }

        if ($kmRestante <= 1000 || ($diasRestantes !== null && $diasRestantes <= 30)) {
            return 'proximo_vencimento';
        }

        return 'em_dia';
    }
}
