<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('solicitacoes_viagem', function (Blueprint $table) {
            $table->dateTime('iniciada_em')->nullable()->after('data_hora')->index();
            $table->dateTime('finalizada_em')->nullable()->after('iniciada_em')->index();
        });

        Schema::table('ocorrencias_viagem', function (Blueprint $table) {
            $table->dateTime('ocorrido_em')->nullable()->after('descricao')->index();
        });

        DB::table('ocorrencias_viagem')->whereNull('ocorrido_em')->update([
            'ocorrido_em' => DB::raw('COALESCE(registrado_em, created_at)'),
        ]);

        Schema::create('api_idempotency_keys', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('idempotency_key', 100);
            $table->string('method', 10);
            $table->string('path', 255);
            $table->char('request_hash', 64);
            $table->unsignedSmallInteger('response_status')->nullable();
            $table->longText('response_body')->nullable();
            $table->timestamp('expires_at')->index();
            $table->timestamps();
            $table->unique(['user_id', 'idempotency_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('api_idempotency_keys');
        Schema::table('ocorrencias_viagem', fn (Blueprint $table) => $table->dropColumn('ocorrido_em'));
        Schema::table('solicitacoes_viagem', fn (Blueprint $table) => $table->dropColumn(['iniciada_em', 'finalizada_em']));
    }
};
