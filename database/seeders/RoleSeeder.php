<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run()
    {
        Role::create(['name' => 'admin', 'description' => 'Administrador']);
        Role::create(['name' => 'operador', 'description' => 'Operador']);
        Role::create(['name' => 'gestor', 'description' => 'Gestor']);
    }
}

