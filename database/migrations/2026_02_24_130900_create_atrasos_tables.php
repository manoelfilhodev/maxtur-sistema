<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('atrasos_viagem', function (Blueprint $table) {
            $table->id();
            $table->foreignId('operador_id')->constrained('operadores')->cascadeOnDelete();
            $table->foreignId('cliente_id')->constrained('clientes')->cascadeOnDelete();
            $table->foreignId('solicitacao_id')->constrained('solicitacoes_viagem')->cascadeOnDelete();
            $table->unsignedInteger('minutos_atraso');
            $table->text('motivo')->nullable();
            $table->foreignId('registrado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('atrasos_passageiro', function (Blueprint $table) {
            $table->id();
            $table->foreignId('operador_id')->constrained('operadores')->cascadeOnDelete();
            $table->foreignId('cliente_id')->constrained('clientes')->cascadeOnDelete();
            $table->foreignId('solicitacao_id')->constrained('solicitacoes_viagem')->cascadeOnDelete();
            $table->foreignId('passageiro_id')->constrained('passageiros')->cascadeOnDelete();
            $table->unsignedInteger('minutos_atraso');
            $table->text('motivo')->nullable();
            $table->foreignId('registrado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('atrasos_passageiro');
        Schema::dropIfExists('atrasos_viagem');
    }
};
