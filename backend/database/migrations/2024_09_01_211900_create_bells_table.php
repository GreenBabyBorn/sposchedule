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
            $table->string('week_day', 2)->nullable();
            $table->date('date')->nullable();
            $table->string('building');
            $table->foreign('building')
                ->references('name')
                ->on('buildings')
                ->onDelete('cascade');
            $table->boolean('is_preset')->default(false);
            $table->string('name_preset')->nullable();
            $table->boolean('published')->default(false)->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('bells');
    }
}
