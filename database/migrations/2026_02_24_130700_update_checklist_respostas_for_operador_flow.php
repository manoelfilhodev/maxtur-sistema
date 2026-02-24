<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('checklist_respostas', function (Blueprint $table) {
            $table->foreignId('operador_id')->nullable()->after('id')->constrained('operadores')->nullOnDelete();
            $table->unsignedInteger('codigo')->nullable()->after('checklist_item_id');
        });

        DB::statement("
            UPDATE checklist_respostas r
            JOIN checklist_itens i ON i.id = r.checklist_item_id
            SET r.codigo = i.codigo
            WHERE r.codigo IS NULL
        ");
    }

    public function down(): void
    {
        Schema::table('checklist_respostas', function (Blueprint $table) {
            $table->dropForeign(['operador_id']);
            $table->dropColumn(['operador_id', 'codigo']);
        });
    }
};
