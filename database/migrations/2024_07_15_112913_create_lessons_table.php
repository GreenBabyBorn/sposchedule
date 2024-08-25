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
        Schema::create('lessons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_id');
            $table->foreignId('schedule_id');
            $table->enum('week_type', ['ЗНАМ', 'ЧИСЛ'])->nullable();
            $table->string('cabinet');
            $table->integer('index')->min(0)->max(10); // Номер пары
            $table->string('building')->default('1'); // Номер корпуса
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lessons');
    }
};
