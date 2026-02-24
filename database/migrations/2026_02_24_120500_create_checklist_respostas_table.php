<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('checklist_respostas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('checklist_id')->constrained('checklists')->cascadeOnDelete();
            $table->foreignId('checklist_item_id')->constrained('checklist_itens')->cascadeOnDelete();
            $table->string('status', 20)->index();
            $table->text('observacao')->nullable();
            $table->string('foto_path')->nullable();
            $table->timestamps();

            $table->unique(['checklist_id', 'checklist_item_id'], 'chk_resposta_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('checklist_respostas');
    }
};
