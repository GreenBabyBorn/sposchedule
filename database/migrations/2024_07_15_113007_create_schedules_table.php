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
        Schema::create('schedules', function (Blueprint $table) {
            $table->id();

            $table->foreignId('group_id');
            $table->foreignId('semester_id');
            $table->dateTimeTz('date')->nullable();
            $table->enum('type', ['main', 'changes']);
            // $table->enum('week_type', ['ЗНАМ', 'ЧИСЛ'])->nullable();
            $table->enum('week_day', ['ПН', 'ВТ', 'СР', 'ЧТ', 'ПТ', 'СБ', 'ВС'])->nullable();
            $table->enum('view_mode', ['table', 'message'])->default('table');
            $table->text('message')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedules');
    }
};
