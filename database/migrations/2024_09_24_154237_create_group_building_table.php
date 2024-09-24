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
        Schema::create('group_building', function (Blueprint $table) {
            $table->unsignedBigInteger('group_id');
            $table->string('building_name');

            // Внешний ключ на таблицу groups
            $table->foreign('group_id')->references('id')->on('groups')->onDelete('cascade');

            // Внешний ключ на таблицу buildings
            $table->foreign('building_name')->references('name')->on('buildings')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('group_building');
    }
};
