<?php

namespace Database\Seeders;

use App\Models\Veiculo;
use Illuminate\Database\Seeder;

class VeiculoSeeder extends Seeder
{
    public function run(): void
    {
        $veiculos = [
            ['placa' => 'ABC1D23', 'modelo' => 'Sprinter', 'capacidade_passageiros' => 18],
            ['placa' => 'XYZ4E56', 'modelo' => 'Van', 'capacidade_passageiros' => 15],
            ['placa' => 'QWE7R89', 'modelo' => 'Micro-onibus', 'capacidade_passageiros' => 25],
        ];

        foreach ($veiculos as $veiculo) {
            Veiculo::query()->updateOrCreate(
                ['placa' => $veiculo['placa']],
                [
                    'operador_id' => 1,
                    'modelo' => $veiculo['modelo'],
                    'capacidade_passageiros' => $veiculo['capacidade_passageiros'],
                    'status_operacional' => 'liberado',
                ]
            );
        }

        if ($this->command) {
            $exemplo = Veiculo::query()->where('placa', 'ABC1D23')->first();
            if ($exemplo) {
                $this->command->info('Veiculo exemplo: '.$exemplo->placa.' | '.$exemplo->modelo.' | cap='.$exemplo->capacidade_passageiros);
            }
        }
    }
}

