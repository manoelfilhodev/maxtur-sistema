<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('atrasos_viagem', function (Blueprint $table) {
            $table->dateTime('ocorrido_em')->nullable()->after('motivo')->index();
        });
        Schema::table('atrasos_passageiro', function (Blueprint $table) {
            $table->dateTime('ocorrido_em')->nullable()->after('motivo')->index();
        });

        DB::table('atrasos_viagem')->whereNull('ocorrido_em')->update(['ocorrido_em' => DB::raw('created_at')]);
        DB::table('atrasos_passageiro')->whereNull('ocorrido_em')->update(['ocorrido_em' => DB::raw('created_at')]);
    }

    public function down(): void
    {
        Schema::table('atrasos_passageiro', fn (Blueprint $table) => $table->dropColumn('ocorrido_em'));
        Schema::table('atrasos_viagem', fn (Blueprint $table) => $table->dropColumn('ocorrido_em'));
    }
};
