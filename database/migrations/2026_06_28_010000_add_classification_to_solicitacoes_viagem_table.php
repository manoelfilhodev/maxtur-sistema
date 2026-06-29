<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('solicitacoes_viagem', function (Blueprint $table) {
            $table->string('tipo_periodo', 20)->default('esporadico')->index();
            $table->string('natureza', 20)->default('programada')->index();
        });
    }

    public function down(): void
    {
        Schema::table('solicitacoes_viagem', function (Blueprint $table) {
            $table->dropColumn(['tipo_periodo', 'natureza']);
        });
    }
};
