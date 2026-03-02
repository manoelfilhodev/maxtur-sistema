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
            if (!Schema::hasColumn('users', 'client_id')) {
                $table->foreignId('client_id')->nullable()->after('cliente_id')->constrained('clientes')->nullOnDelete();
            }
        });

        if (Schema::hasColumn('users', 'client_id') && Schema::hasColumn('users', 'cliente_id')) {
            DB::statement('UPDATE users SET client_id = cliente_id WHERE client_id IS NULL AND cliente_id IS NOT NULL');
            DB::statement('UPDATE users SET cliente_id = client_id WHERE cliente_id IS NULL AND client_id IS NOT NULL');
        }

        if (Schema::hasColumn('users', 'role')) {
            DB::statement("
                UPDATE users
                SET role = CASE
                    WHEN role = 'admin' THEN 'MASTER'
                    WHEN role = 'cliente' THEN CASE WHEN COALESCE(client_id, cliente_id) IS NULL THEN 'MASTER' ELSE 'CLIENT_ADMIN' END
                    WHEN role = 'motorista' THEN 'MASTER'
                    WHEN role IN ('MASTER', 'CLIENT_ADMIN', 'CLIENT_USER') THEN role
                    ELSE CASE WHEN COALESCE(client_id, cliente_id) IS NULL THEN 'MASTER' ELSE 'CLIENT_USER' END
                END
            ");
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'client_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropForeign(['client_id']);
                $table->dropColumn('client_id');
            });
        }
    }
};

