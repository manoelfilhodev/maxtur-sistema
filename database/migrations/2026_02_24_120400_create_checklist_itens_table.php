<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('checklist_itens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('checklist_id')->nullable()->constrained('checklists')->nullOnDelete();
            $table->unsignedInteger('codigo')->unique();
            $table->string('titulo');
            $table->text('como_verificar')->nullable();
            $table->boolean('ativo')->default(true)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('checklist_itens');
    }
};
