<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use InvalidArgumentException;

class ImageBase64Service
{
    private const MAX_BYTES = 4 * 1024 * 1024;

    private const ALLOWED_MIME_EXTENSIONS = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];

    public function saveChecklistItemBase64(int $checklistId, string $codigo, string $base64): string
    {
        return $this->savePublicImage(
            $base64,
            "checklists/{$checklistId}/itens/{$codigo}"
        );
    }

    public function savePublicImage(string $base64, string $directory): string
    {
        [$binary, $extension] = $this->decodeAndValidate($base64);

        $filename = now()->format('Ymd_His').'_'.Str::random(8).'.'.$extension;
        $relativePath = trim($directory, '/').'/'.$filename;

        Storage::disk('public')->put($relativePath, $binary);

        return 'storage/'.$relativePath;
    }

    private function decodeAndValidate(string $base64): array
    {
        if (str_contains($base64, ',')) {
            [, $data] = explode(',', $base64, 2);
        } else {
            $data = $base64;
        }

        $data = preg_replace('/\s+/', '', $data) ?? '';
        if ($data === '') {
            throw new InvalidArgumentException('Imagem base64 invalida.');
        }

        $estimatedBytes = (int) ((strlen($data) * 3) / 4);
        if ($estimatedBytes > self::MAX_BYTES) {
            throw new InvalidArgumentException('Imagem excede o tamanho maximo permitido.');
        }

        $binary = base64_decode($data, true);
        if ($binary === false) {
            throw new InvalidArgumentException('Imagem base64 invalida.');
        }

        if (strlen($binary) > self::MAX_BYTES) {
            throw new InvalidArgumentException('Imagem excede o tamanho maximo permitido.');
        }

        $mime = (new \finfo(FILEINFO_MIME_TYPE))->buffer($binary);
        $extension = self::ALLOWED_MIME_EXTENSIONS[$mime] ?? null;
        if (!$extension || @getimagesizefromstring($binary) === false) {
            throw new InvalidArgumentException('Formato de imagem nao permitido.');
        }

        return [$binary, $extension];
    }
}
