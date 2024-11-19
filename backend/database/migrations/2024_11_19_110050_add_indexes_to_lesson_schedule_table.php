<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('lesson_schedule', function (Blueprint $table) {
            // Индексы для отдельных столбцов
            $table->index('lesson_id');
            $table->index('schedule_id');

            // Составной индекс
            $table->index(['lesson_id', 'schedule_id']);

            // Если нужен уникальный составной индекс
            // $table->unique(['lesson_id', 'schedule_id']);
        });
    }

    public function down(): void
    {
        Schema::table('lesson_schedule', function (Blueprint $table) {
            // Удаление индексов
            $table->dropIndex(['lesson_id']); // Удаление индекса lesson_id
            $table->dropIndex(['schedule_id']); // Удаление индекса schedule_id
            $table->dropIndex(['lesson_id', 'schedule_id']); // Удаление составного индекса

            // Если был создан уникальный индекс
            // $table->dropUnique(['lesson_id', 'schedule_id']);
        });
    }
};
