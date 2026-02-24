<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChecklistItem extends Model
{
    protected $table = 'checklist_itens';

    protected $fillable = ['codigo','titulo','como_verificar','ativo'];

    protected $casts = [
        'ativo' => 'boolean',
    ];

    public function scopeAtivos($q)
    {
        return $q->where('ativo', true)->orderBy('codigo');
    }
    
    public function checklist(): BelongsTo
    {
        return $this->belongsTo(Checklist::class, 'checklist_id', 'id');
    }
}
