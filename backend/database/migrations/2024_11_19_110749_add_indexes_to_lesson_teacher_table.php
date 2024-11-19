<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('lesson_teacher', function (Blueprint $table) {
            // Индексы для отдельных столбцов
            $table->index('teacher_id');
            $table->index('lesson_id');

            // Составной индекс
            $table->index(['teacher_id', 'lesson_id']);

            // Если нужна уникальность для пар (teacher_id, lesson_id)
            // $table->unique(['teacher_id', 'lesson_id']);
        });
    }

    public function down(): void
    {
        Schema::table('lesson_teacher', function (Blueprint $table) {
            // Удаление индексов
            $table->dropIndex(['teacher_id']); // Удаление индекса teacher_id
            $table->dropIndex(['lesson_id']); // Удаление индекса lesson_id
            $table->dropIndex(['teacher_id', 'lesson_id']); // Удаление составного индекса

            // Если был создан уникальный индекс
            // $table->dropUnique(['teacher_id', 'lesson_id']);
        });
    }
};
