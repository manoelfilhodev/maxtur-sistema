<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('solicitacoes_viagem', function (Blueprint $table) {
            $table->id();
            $table->foreignId('operador_id')->constrained('operadores')->cascadeOnDelete();
            $table->foreignId('cliente_id')->constrained('clientes')->cascadeOnDelete();
            $table->string('origem');
            $table->string('destino');
            $table->timestamp('data_hora')->index();
            $table->unsignedInteger('passageiros_previstos')->default(0);
            $table->text('observacao')->nullable();
            $table->enum('status', ['solicitada', 'em_analise', 'aprovada', 'programada', 'checklist_pendente', 'pronta_para_execucao', 'em_andamento', 'atrasada', 'finalizada', 'cancelada', 'bloqueada'])
                ->default('solicitada')
                ->index();
            $table->timestamps();
        });

        Schema::create('solicitacao_passageiros', function (Blueprint $table) {
            $table->id();
            $table->foreignId('operador_id')->constrained('operadores')->cascadeOnDelete();
            $table->foreignId('solicitacao_id')->constrained('solicitacoes_viagem')->cascadeOnDelete();
            $table->foreignId('passageiro_id')->constrained('passageiros')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['solicitacao_id', 'passageiro_id']);
        });

        Schema::create('solicitacao_atribuicoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('operador_id')->constrained('operadores')->cascadeOnDelete();
            $table->foreignId('solicitacao_id')->constrained('solicitacoes_viagem')->cascadeOnDelete();
            $table->foreignId('veiculo_id')->constrained('veiculos')->cascadeOnDelete();
            $table->foreignId('motorista_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('atribuido_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('atribuido_em')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('solicitacao_atribuicoes');
        Schema::dropIfExists('solicitacao_passageiros');
        Schema::dropIfExists('solicitacoes_viagem');
    }
};
