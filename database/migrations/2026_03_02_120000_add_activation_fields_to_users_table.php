<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('activation_token', 128)->nullable()->unique()->after('remember_token');
            $table->dateTime('activation_expires_at')->nullable()->after('activation_token')->index();
            $table->dateTime('activated_at')->nullable()->after('activation_expires_at')->index();
        });

        DB::table('users')
            ->whereNull('activated_at')
            ->whereIn('role', ['CLIENT_ADMIN', 'CLIENT_USER', 'cliente'])
            ->whereNotNull('password')
            ->update(['activated_at' => now()]);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['activation_token']);
            $table->dropColumn(['activation_token', 'activation_expires_at', 'activated_at']);
        });
    }
};
