<?php

namespace Database\Seeders;

use App\Models\Cliente;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UsuarioHomologacaoSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->environment('production')) {
            $this->command?->warn('UsuarioHomologacaoSeeder ignorado em producao.');

            return;
        }

        $adminPassword = env('HOMOLOG_ADMIN_PASSWORD') ?: Str::random(32);
        $defaultPassword = env('HOMOLOG_DEFAULT_PASSWORD') ?: Str::random(32);

        User::query()->updateOrCreate(
            ['email' => 'dev@systex.com.br'],
            [
                'name' => 'Dev Systex',
                'operador_id' => 1,
                'cliente_id' => null,
                'role' => 'admin',
                'ativo' => true,
                'password' => Hash::make($adminPassword),
            ]
        );

        $mapaClientes = Cliente::query()
            ->where('operador_id', 1)
            ->whereIn('nome_fantasia', ['Cliente Alpha', 'Cliente Beta', 'Cliente Gamma'])
            ->get()
            ->keyBy('nome_fantasia');

        $usuariosCliente = [
            ['email' => 'cliente.alpha@systex.com', 'name' => 'Usuario Cliente Alpha', 'cliente' => 'Cliente Alpha'],
            ['email' => 'cliente.beta@systex.com', 'name' => 'Usuario Cliente Beta', 'cliente' => 'Cliente Beta'],
            ['email' => 'cliente.gamma@systex.com', 'name' => 'Usuario Cliente Gamma', 'cliente' => 'Cliente Gamma'],
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
                    'password' => Hash::make($defaultPassword),
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
                    'password' => Hash::make($defaultPassword),
                ]
            );
        }

        if ($this->command) {
            $emails = User::query()
                ->whereIn('email', [
                    'dev@systex.com.br',
                    'cliente.alpha@systex.com',
                    'cliente.beta@systex.com',
                    'cliente.gamma@systex.com',
                    'motorista1@systex.com',
                    'motorista2@systex.com',
                    'motorista3@systex.com',
                ])
                ->orderBy('email')
                ->pluck('email')
                ->implode(', ');

            $this->command->info('Emails homologacao: '.$emails);
            $this->command->warn('Senhas de homologacao devem ser definidas via HOMOLOG_ADMIN_PASSWORD e HOMOLOG_DEFAULT_PASSWORD.');
        }
    }
}
