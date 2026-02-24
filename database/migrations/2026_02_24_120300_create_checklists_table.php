<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('checklists', function (Blueprint $table) {
            $table->id();
            $table->string('veiculo_identificacao', 50)->nullable();
            $table->string('modelo_veiculo', 120)->nullable();
            $table->string('placa', 15)->nullable()->index();
            $table->date('data')->nullable()->index();
            $table->string('motorista_nome', 120)->nullable();
            $table->string('empresa_fornecedora', 120)->nullable();
            $table->string('inspecionado_por', 120)->nullable();
            $table->string('responsavel_nome', 120)->nullable();
            $table->string('responsavel_funcao', 120)->nullable();
            $table->text('comentarios_motorista')->nullable();
            $table->string('status', 20)->default('pendente')->index();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('checklists');
    }
};
