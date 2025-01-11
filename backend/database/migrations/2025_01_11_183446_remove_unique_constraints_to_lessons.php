<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class () extends Migration {
    public function up(): void
    {
        DB::statement("DROP INDEX IF EXISTS lessons_schedule_index_unique");
    }

    public function down(): void
    {
        DB::statement('CREATE UNIQUE INDEX lessons_schedule_index_unique ON lessons(schedule_id, index, COALESCE(week_type, \'NULL\'))');
    }
};