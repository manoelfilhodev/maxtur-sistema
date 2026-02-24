<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('checklist_itens', function (Blueprint $table) {
            $table->string('categoria', 80)->nullable()->after('titulo');
            $table->unsignedInteger('ordem')->nullable()->after('categoria');
        });

        DB::statement('UPDATE checklist_itens SET ordem = codigo WHERE ordem IS NULL');
    }

    public function down(): void
    {
        Schema::table('checklist_itens', function (Blueprint $table) {
            $table->dropColumn(['categoria', 'ordem']);
        });
    }
};
