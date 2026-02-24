<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ChecklistItensSeeder extends Seeder
{
    public function run(): void
    {
        $itens = [
            1  => 'Cinto de segurança',
            2  => 'Extintor',
            3  => 'Porta e trava de segurança',
            4  => 'Tacógrafo (quando aplicável)',
            5  => 'Limpador de para-brisa',
            6  => 'Setas / sinalização',
            7  => 'Câmera de ré (quando aplicável)',
            8  => 'Faróis / iluminação',
            9  => 'Pneus / estepe',
            10 => 'Freios',
            11 => 'Triângulo',
            12 => 'Macaco / chave de roda',
            13 => 'Buzina',
            14 => 'Retrovisores',
            15 => 'Condições gerais do interior (bancos/limpeza/segurança)',
        ];

        foreach ($itens as $codigo => $titulo) {
            DB::table('checklist_itens')->updateOrInsert(
                ['codigo' => $codigo],
                ['titulo' => $titulo, 'ativo' => true, 'updated_at' => now(), 'created_at' => now()]
            );
        }
    }
}
