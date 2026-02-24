<?php

namespace App\Services;

use App\Models\NotificationMvp;
use App\Models\User;

class NotificationService
{
    public function notifyAdmins(int $operadorId, string $type, string $title, string $body, array $payload = []): NotificationMvp
    {
        $notification = NotificationMvp::create([
            'operador_id' => $operadorId,
            'type' => $type,
            'title' => $title,
            'body' => $body,
            'payload_json' => $payload,
        ]);

        $adminIds = User::query()
            ->where('operador_id', $operadorId)
            ->where('role', 'admin')
            ->pluck('id')
            ->all();

        if ($adminIds) {
            $notification->users()->attach($adminIds);
        }

        return $notification;
    }
}
