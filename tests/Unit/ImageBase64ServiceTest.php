<?php

namespace Tests\Unit;

use App\Services\ImageBase64Service;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;
use Tests\TestCase;

class ImageBase64ServiceTest extends TestCase
{
    public function test_it_saves_valid_base64_image_to_public_disk(): void
    {
        Storage::fake('public');

        $base64 = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/p9sAAAAASUVORK5CYII=';

        $path = app(ImageBase64Service::class)->savePublicImage($base64, 'checklists/1/itens/1');

        $this->assertStringStartsWith('storage/checklists/1/itens/1/', $path);

        Storage::disk('public')->assertExists(str_replace('storage/', '', $path));
    }

    public function test_it_rejects_invalid_base64_image(): void
    {
        $this->expectException(InvalidArgumentException::class);

        app(ImageBase64Service::class)->savePublicImage('nao-e-imagem', 'checklists/1/itens/1');
    }
}
