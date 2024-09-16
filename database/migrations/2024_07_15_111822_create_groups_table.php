<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('groups', function (Blueprint $table) {
            $table->id();
            $table->integer('course');
            $table->string('index');
            $table->string('specialization');
            $table->string('name')->unique();
            $table->string('building')->nullable();  // Поле для внешнего ключа

            // Настраиваем внешний ключ, связывающий building_name с полем name в таблице buildings
            $table->foreign('building')->references('name')->on('buildings');
            $table->timestamps();
        });

        // Создаем функцию для генерации поля name
        DB::statement('
            CREATE OR REPLACE FUNCTION generate_group_name() RETURNS TRIGGER AS $$
            BEGIN
                NEW.name := NEW.specialization || \'-\' || NEW.course || NEW.index;
                RETURN NEW;
            END;
            $$ LANGUAGE plpgsql;
        ');

        // Создаем триггер, который будет вызывать функцию при вставке или обновлении записей
        DB::statement('
            CREATE TRIGGER trigger_generate_group_name
            BEFORE INSERT OR UPDATE ON groups
            FOR EACH ROW
            EXECUTE FUNCTION generate_group_name();
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP TRIGGER IF EXISTS trigger_generate_group_name ON groups;');
        DB::statement('DROP FUNCTION IF EXISTS generate_group_name();');

        Schema::dropIfExists('groups');
    }
};
