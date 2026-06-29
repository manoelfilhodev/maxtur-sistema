<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApiIdempotencyKey extends Model
{
    protected $fillable = [
        'user_id', 'idempotency_key', 'method', 'path', 'request_hash',
        'response_status', 'response_body', 'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'response_status' => 'integer',
    ];
}
