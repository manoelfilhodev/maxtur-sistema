<?php

namespace App\Services;

use Illuminate\Support\Str;

class ImageBase64Service
{
    public function saveChecklistItemBase64(int $checklistId, string $codigo, string $base64): string
    {
        if (str_contains($base64, ',')) {
            [, $data] = explode(',', $base64, 2);
        } else {
            $data = $base64;
        }

        $binary = base64_decode($data);
        if ($binary === false) {
            throw new \RuntimeException('Imagem base64 invalida.');
        }

        $filename = now()->format('Ymd_His').'_'.Str::random(8).'.jpg';
        $relativePath = "checklists/{$checklistId}/itens/{$codigo}/{$filename}";
        $fullPath = storage_path('app/public/'.$relativePath);

        if (!is_dir(dirname($fullPath))) {
            mkdir(dirname($fullPath), 0755, true);
        }

        file_put_contents($fullPath, $binary);

        return 'storage/'.$relativePath;
    }
}
