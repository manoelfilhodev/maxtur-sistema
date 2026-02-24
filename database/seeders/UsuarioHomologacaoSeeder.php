<?php

namespace Database\Seeders;

use App\Models\Cliente;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsuarioHomologacaoSeeder extends Seeder
{
    public function run(): void
    {
        User::query()->updateOrCreate(
            ['email' => 'dev@systex.com.br'],
            [
                'name' => 'Dev Systex',
                'operador_id' => 1,
                'cliente_id' => null,
                'role' => 'admin',
                'ativo' => true,
                'password' => Hash::make('nVbb261214!@'),
            ]
        );

        $mapaClientes = Cliente::query()
            ->where('operador_id', 1)
            ->whereIn('nome_fantasia', ['Cliente Alpha', 'Cliente Beta', 'Cliente Gama'])
            ->get()
            ->keyBy('nome_fantasia');

        $usuariosCliente = [
            ['email' => 'cliente.alpha@systex.com', 'name' => 'Usuario Cliente Alpha', 'cliente' => 'Cliente Alpha'],
            ['email' => 'cliente.beta@systex.com', 'name' => 'Usuario Cliente Beta', 'cliente' => 'Cliente Beta'],
            ['email' => 'cliente.gama@systex.com', 'name' => 'Usuario Cliente Gama', 'cliente' => 'Cliente Gama'],
        ];

        foreach ($usuariosCliente as $usuario) {
            $cliente = $mapaClientes->get($usuario['cliente']);
            if (!$cliente) {
                continue;
            }

            User::query()->updateOrCreate(
                ['email' => $usuario['email']],
                [
                    'name' => $usuario['name'],
                    'operador_id' => 1,
                    'cliente_id' => $cliente->id,
                    'role' => 'cliente',
                    'ativo' => true,
                    'password' => Hash::make('123456'),
                ]
            );
        }

        $motoristas = [
            ['email' => 'motorista1@systex.com', 'name' => 'Motorista 1'],
            ['email' => 'motorista2@systex.com', 'name' => 'Motorista 2'],
            ['email' => 'motorista3@systex.com', 'name' => 'Motorista 3'],
        ];

        foreach ($motoristas as $motorista) {
            User::query()->updateOrCreate(
                ['email' => $motorista['email']],
                [
                    'name' => $motorista['name'],
                    'operador_id' => 1,
                    'cliente_id' => null,
                    'role' => 'motorista',
                    'ativo' => true,
                    'password' => Hash::make('123456'),
                ]
            );
        }

        if ($this->command) {
            $emails = User::query()
                ->whereIn('email', [
                    'dev@systex.com.br',
                    'cliente.alpha@systex.com',
                    'cliente.beta@systex.com',
                    'cliente.gama@systex.com',
                    'motorista1@systex.com',
                    'motorista2@systex.com',
                    'motorista3@systex.com',
                ])
                ->orderBy('email')
                ->pluck('email')
                ->implode(', ');

            $this->command->info('Emails homologacao: '.$emails);
        }
    }
}

