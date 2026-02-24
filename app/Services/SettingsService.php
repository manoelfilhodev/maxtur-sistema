<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class SettingsService
{
    public function get(string $key, ?int $clienteId = null, mixed $default = null): mixed
    {
        $row = DB::table('settings')->where('key', $key)->first();

        if (!$row || !isset($row->value)) {
            return $default;
        }

        $decoded = json_decode((string) $row->value, true);

        return json_last_error() === JSON_ERROR_NONE ? $decoded : $row->value;
    }

    public function set(string $key, mixed $value, ?int $clienteId = null, ?int $updatedBy = null): void
    {
        $encoded = is_string($value) ? $value : json_encode($value, JSON_UNESCAPED_UNICODE);

        DB::table('settings')->updateOrInsert(
            ['key' => $key],
            ['value' => $encoded]
        );
    }
}
