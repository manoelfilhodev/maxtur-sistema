<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('checklists', function (Blueprint $table) {
            $table->foreignId('operador_id')->nullable()->after('id')->constrained('operadores')->nullOnDelete();
            $table->foreignId('veiculo_id')->nullable()->after('operador_id')->constrained('veiculos')->nullOnDelete();
            $table->foreignId('motorista_id')->nullable()->after('veiculo_id')->constrained('users')->nullOnDelete();
            $table->enum('resultado', ['apto', 'nao_conforme'])->nullable()->after('status');
            $table->timestamp('started_at')->nullable()->after('resultado');
            $table->timestamp('finished_at')->nullable()->after('started_at');
        });

        DB::statement("UPDATE checklists SET status = 'finalizado' WHERE status IN ('aprovado','reprovado')");
        DB::statement("UPDATE checklists SET status = 'em_andamento' WHERE status NOT IN ('finalizado')");
    }

    public function down(): void
    {
        Schema::table('checklists', function (Blueprint $table) {
            $table->dropForeign(['operador_id']);
            $table->dropForeign(['veiculo_id']);
            $table->dropForeign(['motorista_id']);
            $table->dropColumn(['operador_id', 'veiculo_id', 'motorista_id', 'resultado', 'started_at', 'finished_at']);
        });
    }
};
