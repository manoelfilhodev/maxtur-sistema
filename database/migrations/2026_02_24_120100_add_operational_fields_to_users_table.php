<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('cpf', 14)->nullable()->unique()->after('email');
            $table->string('cargo', 100)->nullable()->after('cpf');
            $table->string('nivel', 100)->nullable()->after('cargo');
            $table->foreignId('cliente_id')->nullable()->after('nivel')->constrained('clientes')->nullOnDelete();
            $table->boolean('ativo')->default(true)->after('cliente_id');
            $table->string('jornada_id', 50)->nullable()->after('ativo');
            $table->string('turno_id', 50)->nullable()->after('jornada_id');
            $table->boolean('ferias_ativo')->default(false)->after('turno_id');
            $table->date('ferias_inicio')->nullable()->after('ferias_ativo');
            $table->date('ferias_fim')->nullable()->after('ferias_inicio');
            $table->string('foto')->nullable()->after('ferias_fim');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['cliente_id']);
            $table->dropColumn([
                'cpf',
                'cargo',
                'nivel',
                'cliente_id',
                'ativo',
                'jornada_id',
                'turno_id',
                'ferias_ativo',
                'ferias_inicio',
                'ferias_fim',
                'foto',
            ]);
        });
    }
};
