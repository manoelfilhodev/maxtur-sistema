<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clientes', function (Blueprint $table) {
            $table->id();
            $table->string('razao_social');
            $table->string('nome_fantasia')->nullable();
            $table->string('documento', 30)->nullable()->index();
            $table->string('email')->nullable()->index();
            $table->string('telefone', 50)->nullable();
            $table->string('whatsapp', 50)->nullable();
            $table->string('cidade', 120)->nullable();
            $table->string('uf', 2)->nullable();
            $table->text('observacoes')->nullable();
            $table->boolean('ativo')->default(true)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clientes');
    }
};
