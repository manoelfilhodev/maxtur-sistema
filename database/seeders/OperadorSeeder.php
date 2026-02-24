<?php

namespace Database\Seeders;

use App\Models\Operador;
use Illuminate\Database\Seeder;

class OperadorSeeder extends Seeder
{
    public function run(): void
    {
        Operador::query()->updateOrCreate(
            ['id' => 1],
            [
                'nome' => 'Operador Systex',
                'cnpj' => '00.000.000/0001-00',
                'ativo' => true,
            ]
        );
    }
}

