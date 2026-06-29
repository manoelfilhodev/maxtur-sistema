<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MotoristaDocumento extends Model
{
    protected $fillable = ['motorista_id', 'tipo', 'nome_original', 'caminho', 'mime_type', 'tamanho'];

    public function motorista(): BelongsTo
    {
        return $this->belongsTo(User::class, 'motorista_id');
    }
}
