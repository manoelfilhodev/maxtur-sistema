<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('veiculos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('operador_id')->constrained('operadores')->cascadeOnDelete();
            $table->string('placa', 15)->unique();
            $table->string('modelo', 120)->nullable();
            $table->unsignedInteger('capacidade_passageiros')->default(0);
            $table->enum('status_operacional', ['liberado', 'bloqueado'])->default('liberado')->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('veiculos');
    }
};
