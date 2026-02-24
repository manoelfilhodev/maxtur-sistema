<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('operador_id')->constrained('operadores')->cascadeOnDelete();
            $table->enum('type', ['VIAGEM_SOLICITADA', 'CHECKLIST_REPROVADO'])->index();
            $table->string('title');
            $table->text('body');
            $table->json('payload_json')->nullable();
            $table->timestamps();
        });

        Schema::create('notification_users', function (Blueprint $table) {
            $table->foreignId('notification_id')->constrained('notifications')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamp('read_at')->nullable();
            $table->primary(['notification_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_users');
        Schema::dropIfExists('notifications');
    }
};
