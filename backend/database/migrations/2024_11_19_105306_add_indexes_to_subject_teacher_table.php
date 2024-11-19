<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('subject_teacher', function (Blueprint $table) {
            $table->index('teacher_id');
            $table->index('subject_id');

            // Составной индекс
            $table->index(['teacher_id', 'subject_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subject_teacher', function (Blueprint $table) {
            // Удаление добавленных индексов
            $table->dropIndex(['teacher_id']); // Удаление индекса teacher_id
            $table->dropIndex(['subject_id']); // Удаление индекса subject_id
            $table->dropIndex(['teacher_id', 'subject_id']); // Удаление составного индекса

            // Если был создан уникальный индекс
            // $table->dropUnique(['teacher_id', 'subject_id']);
        });
    }
};
