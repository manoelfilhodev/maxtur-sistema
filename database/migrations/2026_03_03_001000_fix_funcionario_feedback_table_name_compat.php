<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $hasSingular = Schema::hasTable('funcionario_feedback');
        $hasPlural = Schema::hasTable('funcionario_feedbacks');

        if ($hasSingular && !$hasPlural) {
            Schema::rename('funcionario_feedback', 'funcionario_feedbacks');
        }
    }

    public function down(): void
    {
        $hasSingular = Schema::hasTable('funcionario_feedback');
        $hasPlural = Schema::hasTable('funcionario_feedbacks');

        if (!$hasSingular && $hasPlural) {
            Schema::rename('funcionario_feedbacks', 'funcionario_feedback');
        }
    }
};
