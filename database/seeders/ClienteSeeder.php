<?php

namespace Database\Seeders;

use App\Models\Cliente;
use Illuminate\Database\Seeder;

class ClienteSeeder extends Seeder
{
    public function run(): void
    {
        $clientes = [
            [
                'razao_social' => 'Cliente Alpha Ltda',
                'nome_fantasia' => 'Cliente Alpha',
                'cnpj' => '11.111.111/0001-11',
                'email' => 'contato.alpha@systex.com',
                'telefone' => '(11) 90000-0001',
                'endereco' => 'Rua Alpha, 100',
            ],
            [
                'razao_social' => 'Cliente Beta Ltda',
                'nome_fantasia' => 'Cliente Beta',
                'cnpj' => '22.222.222/0001-22',
                'email' => 'contato.beta@systex.com',
                'telefone' => '(11) 90000-0002',
                'endereco' => 'Rua Beta, 200',
            ],
            [
                'razao_social' => 'Cliente Gama Ltda',
                'nome_fantasia' => 'Cliente Gama',
                'cnpj' => '33.333.333/0001-33',
                'email' => 'contato.gama@systex.com',
                'telefone' => '(11) 90000-0003',
                'endereco' => 'Rua Gama, 300',
            ],
        ];

        foreach ($clientes as $cliente) {
            Cliente::query()->updateOrCreate(
                [
                    'operador_id' => 1,
                    'cnpj' => $cliente['cnpj'],
                ],
                array_merge($cliente, ['operador_id' => 1, 'ativo' => true])
            );
        }
    }
}

