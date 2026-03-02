<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('funcionario_feedbacks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('operador_id')->constrained('operadores')->cascadeOnDelete();
            $table->foreignId('funcionario_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('tipo', 20);
            $table->text('mensagem');
            $table->string('status', 20)->default('novo');
            $table->timestamps();

            $table->index(['operador_id', 'status']);
            $table->index(['operador_id', 'funcionario_user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('funcionario_feedbacks');
    }
};
