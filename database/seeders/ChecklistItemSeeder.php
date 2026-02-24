<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class ChecklistItemSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(ChecklistItensSeeder::class);
    }
}

