<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class NotificationMvp extends Model
{
    protected $table = 'notifications';

    protected $fillable = [
        'operador_id',
        'type',
        'title',
        'body',
        'payload_json',
    ];

    protected $casts = [
        'payload_json' => 'array',
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'notification_users', 'notification_id', 'user_id')
            ->withPivot('read_at');
    }
}
