<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddUniqueConstraintsToSchedules extends Migration
{
    public function up(): void
    {
        Schema::table('schedules', function (Blueprint $table) {
            // Индексы добавляются через raw SQL
        });

        // Уникальный индекс для записей с type = 'changes'
        DB::statement("
            CREATE UNIQUE INDEX schedules_unique_changes
            ON schedules (group_id, date)
            WHERE type = 'changes'
        ");

        // Уникальный индекс для записей с type = 'main'
        DB::statement("
            CREATE UNIQUE INDEX schedules_unique_main
            ON schedules (group_id, week_day, semester_id)
            WHERE type = 'main'
        ");
    }

    public function down(): void
    {
        // Удаление индексов при откате миграции
        DB::statement("DROP INDEX IF EXISTS schedules_unique_changes");
        DB::statement("DROP INDEX IF EXISTS schedules_unique_main");
    }
}
