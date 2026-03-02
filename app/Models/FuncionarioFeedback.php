<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FuncionarioFeedback extends Model
{
    protected $table = 'funcionario_feedbacks';

    protected $fillable = [
        'operador_id',
        'funcionario_user_id',
        'tipo',
        'mensagem',
        'status',
    ];

    public function operador(): BelongsTo
    {
        return $this->belongsTo(Operador::class, 'operador_id');
    }

    public function funcionario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'funcionario_user_id');
    }
}
