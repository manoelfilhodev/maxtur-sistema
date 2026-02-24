<?php

namespace Database\Seeders;

use App\Models\Cliente;
use App\Models\Passageiro;
use Illuminate\Database\Seeder;

class PassageiroSeeder extends Seeder
{
    public function run(): void
    {
        $clientes = Cliente::query()
            ->where('operador_id', 1)
            ->whereIn('nome_fantasia', ['Cliente Alpha', 'Cliente Beta', 'Cliente Gama'])
            ->get();

        foreach ($clientes as $cliente) {
            for ($i = 1; $i <= 3; $i++) {
                $prefixo = strtoupper(substr((string) $cliente->nome_fantasia, -1));
                $documento = $prefixo.str_pad((string) $i, 5, '0', STR_PAD_LEFT);

                Passageiro::query()->updateOrCreate(
                    [
                        'operador_id' => 1,
                        'cliente_id' => $cliente->id,
                        'documento' => $documento,
                    ],
                    [
                        'nome' => 'Passageiro '.$cliente->nome_fantasia.' '.$i,
                        'telefone' => '(11) 98888-10'.str_pad((string) $i, 2, '0', STR_PAD_LEFT),
                        'ativo' => true,
                    ]
                );
            }
        }
    }
}

