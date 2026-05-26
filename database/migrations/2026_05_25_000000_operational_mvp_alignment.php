<?php

use App\Support\ViagemStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE solicitacoes_viagem MODIFY status ENUM('aberta','solicitada','em_analise','aprovada','programada','checklist_pendente','pronta_para_execucao','em_andamento','atrasada','realizada','finalizada','cancelada','rejeitada','bloqueada') NOT NULL DEFAULT 'solicitada'");
        }

        DB::table('solicitacoes_viagem')->where('status', 'aberta')->update(['status' => ViagemStatus::SOLICITADA]);
        DB::table('solicitacoes_viagem')->where('status', 'realizada')->update(['status' => ViagemStatus::FINALIZADA]);
        DB::table('solicitacoes_viagem')->where('status', 'rejeitada')->update(['status' => ViagemStatus::CANCELADA]);

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE solicitacoes_viagem MODIFY status ENUM('solicitada','em_analise','aprovada','programada','checklist_pendente','pronta_para_execucao','em_andamento','atrasada','finalizada','cancelada','bloqueada') NOT NULL DEFAULT 'solicitada'");
        }

        Schema::table('checklists', function (Blueprint $table) {
            if (!Schema::hasColumn('checklists', 'solicitacao_id')) {
                $table->foreignId('solicitacao_id')
                    ->nullable()
                    ->after('operador_id')
                    ->constrained('solicitacoes_viagem')
                    ->nullOnDelete();
            }
        });

        if (!Schema::hasTable('ocorrencias_viagem')) {
            Schema::create('ocorrencias_viagem', function (Blueprint $table) {
                $table->id();
                $table->foreignId('operador_id')->constrained('operadores')->cascadeOnDelete();
                $table->foreignId('cliente_id')->constrained('clientes')->cascadeOnDelete();
                $table->foreignId('solicitacao_id')->constrained('solicitacoes_viagem')->cascadeOnDelete();
                $table->string('tipo', 80);
                $table->text('descricao');
                $table->string('evidencia_path')->nullable();
                $table->foreignId('registrado_por')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('registrado_em')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('ocorrencias_viagem');

        Schema::table('checklists', function (Blueprint $table) {
            if (Schema::hasColumn('checklists', 'solicitacao_id')) {
                $table->dropConstrainedForeignId('solicitacao_id');
            }
        });

        DB::table('solicitacoes_viagem')->where('status', ViagemStatus::SOLICITADA)->update(['status' => 'aberta']);
        DB::table('solicitacoes_viagem')->where('status', ViagemStatus::FINALIZADA)->update(['status' => 'realizada']);
        DB::table('solicitacoes_viagem')->whereIn('status', [
            ViagemStatus::CHECKLIST_PENDENTE,
            ViagemStatus::PRONTA_PARA_EXECUCAO,
            ViagemStatus::EM_ANDAMENTO,
            ViagemStatus::ATRASADA,
            ViagemStatus::BLOQUEADA,
        ])->update(['status' => 'programada']);

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE solicitacoes_viagem MODIFY status ENUM('aberta','em_analise','aprovada','programada','realizada','cancelada','rejeitada') NOT NULL DEFAULT 'aberta'");
        }
    }
};
