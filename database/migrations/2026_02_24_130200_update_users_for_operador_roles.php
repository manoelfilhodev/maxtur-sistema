<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('operador_id')->nullable()->after('id')->constrained('operadores')->nullOnDelete();
            $table->string('role', 20)->default('cliente')->after('cliente_id')->index();
        });

        // Compatibilidade com base legada (cargo -> role)
        DB::statement("
            UPDATE users
            SET role = CASE
                WHEN LOWER(COALESCE(cargo, '')) = 'admin' THEN 'admin'
                WHEN LOWER(COALESCE(cargo, '')) = 'motorista' THEN 'motorista'
                ELSE 'cliente'
            END
        ");
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['operador_id']);
            $table->dropColumn(['operador_id', 'role']);
        });
    }
};
