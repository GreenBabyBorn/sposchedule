<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('group_semester', function (Blueprint $table) {
            // Индексы для отдельных столбцов
            $table->index('group_id');
            $table->index('semester_id');

            // Составной индекс
            $table->index(['group_id', 'semester_id']);

            // Если требуется уникальность для пар (group_id, semester_id)
            // $table->unique(['group_id', 'semester_id']);
        });
    }

    public function down(): void
    {
        Schema::table('group_semester', function (Blueprint $table) {
            // Удаление индексов
            $table->dropIndex(['group_id']); // Удаление индекса group_id
            $table->dropIndex(['semester_id']); // Удаление индекса semester_id
            $table->dropIndex(['group_id', 'semester_id']); // Удаление составного индекса

            // Если был создан уникальный индекс
            // $table->dropUnique(['group_id', 'semester_id']);
        });
    }
};
