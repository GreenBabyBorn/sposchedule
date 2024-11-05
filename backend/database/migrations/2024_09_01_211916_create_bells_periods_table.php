<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBellsPeriodsTable extends Migration
{
    public function up()
    {
        Schema::create('bells_periods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bells_id')->constrained('bells')->onDelete('cascade');
            $table->unsignedSmallInteger('index');
            $table->boolean('has_break');
            $table->time('period_from');
            $table->time('period_to');
            $table->time('period_from_after')->nullable();
            $table->time('period_to_after')->nullable();
            $table->timestamps();

            // Уникальность звонка по индексу
            $table->unique(['bells_id', 'index'], 'unique_bells_index');

            // Проверка на наличие дополнительных полей при наличии перерыва
            // $table->check("(has_break = false OR (has_break = true AND period_from_after IS NOT NULL AND period_to_after IS NOT NULL))");
        });
    }

    public function down()
    {
        Schema::dropIfExists('bells_periods');
    }
}
