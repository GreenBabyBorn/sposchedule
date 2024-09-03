<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBellsTable extends Migration
{
    public function up()
    {
        Schema::create('bells', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['main', 'changes']);
            $table->enum('variant', ['normal', 'reduced']);
            $table->string('week_day', 2)->nullable();
            $table->date('date')->nullable();
            $table->timestamps();

            // Ограничение на целостность расписания
            // $table->check("((type = 'main' AND week_day IS NOT NULL) OR (type = 'changes' AND date IS NOT NULL))");

            // Уникальные ограничения
            $table->unique(['variant', 'week_day'], 'unique_variant_week_day');
            $table->unique(['variant', 'date'], 'unique_variant_date');
        });
    }

    public function down()
    {
        Schema::dropIfExists('bells');
    }
}
