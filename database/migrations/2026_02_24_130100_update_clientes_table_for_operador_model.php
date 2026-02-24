<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            $table->foreignId('operador_id')->nullable()->after('id')->constrained('operadores')->nullOnDelete();
            $table->string('cnpj', 20)->nullable()->after('nome_fantasia');
            $table->string('endereco')->nullable()->after('telefone');
        });
    }

    public function down(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            $table->dropForeign(['operador_id']);
            $table->dropColumn(['operador_id', 'cnpj', 'endereco']);
        });
    }
};
