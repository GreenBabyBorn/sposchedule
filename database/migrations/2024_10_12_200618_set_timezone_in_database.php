<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class () extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Установить временную зону для базы данных на Europe/Moscow
        DB::unprepared('ALTER DATABASE ' . env('DB_DATABASE') . ' SET timezone TO \'' . env('APP_TIMEZONE') . '\';');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Вернуть временную зону обратно на UTC при откате миграции
        DB::unprepared('ALTER DATABASE ' . env('DB_DATABASE') . ' SET timezone TO \'UTC\';');
    }
};
