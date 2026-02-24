<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ChecklistItensSeeder extends Seeder
{
    public function run(): void
    {
        $itens = [
            ['codigo' => 1, 'titulo' => 'Cinto de seguranca', 'categoria' => 'Seguranca'],
            ['codigo' => 2, 'titulo' => 'Extintor', 'categoria' => 'Seguranca'],
            ['codigo' => 3, 'titulo' => 'Porta e trava de seguranca', 'categoria' => 'Seguranca'],
            ['codigo' => 4, 'titulo' => 'Tacografo (quando aplicavel)', 'categoria' => 'Documentacao'],
            ['codigo' => 5, 'titulo' => 'Limpador de para-brisa', 'categoria' => 'Operacional'],
            ['codigo' => 6, 'titulo' => 'Setas e sinalizacao', 'categoria' => 'Operacional'],
            ['codigo' => 7, 'titulo' => 'Camera de re (quando aplicavel)', 'categoria' => 'Operacional'],
            ['codigo' => 8, 'titulo' => 'Farois e iluminacao', 'categoria' => 'Operacional'],
            ['codigo' => 9, 'titulo' => 'Pneus e estepe', 'categoria' => 'Seguranca'],
            ['codigo' => 10, 'titulo' => 'Freios', 'categoria' => 'Seguranca'],
            ['codigo' => 11, 'titulo' => 'Triangulo', 'categoria' => 'Seguranca'],
            ['codigo' => 12, 'titulo' => 'Macaco e chave de roda', 'categoria' => 'Operacional'],
            ['codigo' => 13, 'titulo' => 'Buzina', 'categoria' => 'Operacional'],
            ['codigo' => 14, 'titulo' => 'Retrovisores', 'categoria' => 'Seguranca'],
            ['codigo' => 15, 'titulo' => 'Condicoes gerais do interior', 'categoria' => 'Conforto'],
        ];

        foreach ($itens as $ordem => $item) {
            DB::table('checklist_itens')->updateOrInsert(
                ['codigo' => $item['codigo']],
                [
                    'titulo' => $item['titulo'],
                    'categoria' => $item['categoria'],
                    'ordem' => $ordem + 1,
                    'ativo' => true,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }
    }
}

