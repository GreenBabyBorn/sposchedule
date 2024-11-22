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
        Schema::table('schedules', function (Blueprint $table) {
            $table->index(['type', 'date'], 'idx_schedules_type_date');
            $table->index(['published', 'week_day', 'semester_id'], 'idx_schedules_main_fields');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('schedules', function (Blueprint $table) {
            $table->dropIndex('idx_schedules_type_date');
            $table->dropIndex('idx_schedules_main_fields');
        });
    }
};
