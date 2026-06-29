<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('veiculos', function (Blueprint $table) {
            $table->string('tipo', 20)->default('proprio')->index();
            $table->unsignedSmallInteger('ano')->nullable();
            $table->date('data_documento')->nullable();
            $table->unsignedBigInteger('km_atual')->default(0);
        });

        Schema::create('veiculo_manutencoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('veiculo_id')->constrained('veiculos')->cascadeOnDelete();
            $table->string('item', 120);
            $table->unsignedBigInteger('km_referencia');
            $table->unsignedBigInteger('km_vencimento');
            $table->date('data_vencimento')->nullable();
            $table->text('observacao')->nullable();
            $table->string('status', 30)->default('em_dia')->index();
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->date('cnh_vencimento')->nullable()->index();
            $table->string('tipo_recebimento', 20)->nullable();
            $table->decimal('valor_salario', 12, 2)->nullable();
            $table->decimal('valor_por_viagem', 12, 2)->nullable();
            $table->date('data_admissao')->nullable();
        });

        Schema::create('motorista_documentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('motorista_id')->constrained('users')->cascadeOnDelete();
            $table->string('tipo', 40);
            $table->string('nome_original');
            $table->string('caminho');
            $table->string('mime_type', 100);
            $table->unsignedBigInteger('tamanho');
            $table->timestamps();
            $table->index(['motorista_id', 'tipo']);
        });

        Schema::table('viagens', function (Blueprint $table) {
            $table->string('tipo_periodo', 20)->default('esporadico')->index();
            $table->string('natureza', 20)->default('programada')->index();
        });

        if (Schema::hasColumn('users', 'nivel')) {
            DB::table('users')->whereNull('nivel')->update(['nivel' => DB::raw('COALESCE(role, cargo)')]);
        }
    }

    public function down(): void
    {
        Schema::table('viagens', fn (Blueprint $table) => $table->dropColumn(['tipo_periodo', 'natureza']));
        Schema::dropIfExists('motorista_documentos');
        Schema::table('users', fn (Blueprint $table) => $table->dropColumn([
            'cnh_vencimento', 'tipo_recebimento', 'valor_salario', 'valor_por_viagem', 'data_admissao',
        ]));
        Schema::dropIfExists('veiculo_manutencoes');
        Schema::table('veiculos', fn (Blueprint $table) => $table->dropColumn(['tipo', 'ano', 'data_documento', 'km_atual']));
    }
};
