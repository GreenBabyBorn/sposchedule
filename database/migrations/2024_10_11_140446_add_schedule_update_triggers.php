<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddScheduleUpdateTriggers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Создаем функцию для обновления поля updated_at у schedule
        DB::unprepared('
            CREATE OR REPLACE FUNCTION update_schedule_updated_at()
            RETURNS TRIGGER AS $$
            BEGIN
                -- Обновляем расписание по schedule_id, если оно есть
                IF NEW.schedule_id IS NOT NULL THEN
                    UPDATE schedules
                    SET updated_at = NOW()
                    WHERE id = NEW.schedule_id;
                END IF;
                RETURN NEW;
            END;
            $$ LANGUAGE plpgsql;
        ');

        // Триггер для таблицы lessons
        DB::unprepared('
            CREATE TRIGGER update_schedule_on_lesson_change
            AFTER INSERT OR UPDATE OR DELETE ON lessons
            FOR EACH ROW
            EXECUTE FUNCTION update_schedule_updated_at();
        ');

        // Удаляем триггер для таблицы subjects, так как нет прямой связи с расписанием
        /*
        DB::unprepared('
            CREATE TRIGGER update_schedule_on_subject_change
            AFTER INSERT OR UPDATE OR DELETE ON subjects
            FOR EACH ROW
            EXECUTE FUNCTION update_schedule_updated_at();
        ');
        */

        // Триггер для таблицы lesson_teacher
        DB::unprepared('
            CREATE OR REPLACE FUNCTION update_schedule_from_lesson_teacher()
            RETURNS TRIGGER AS $$
            BEGIN
                -- Обновляем расписание через связь с уроком (lesson_id)
                UPDATE schedules
                SET updated_at = NOW()
                WHERE id = (SELECT schedule_id FROM lessons WHERE id = NEW.lesson_id);
                RETURN NEW;
            END;
            $$ LANGUAGE plpgsql;
        ');

        DB::unprepared('
            CREATE TRIGGER update_schedule_on_teacher_change
            AFTER INSERT OR UPDATE OR DELETE ON lesson_teacher
            FOR EACH ROW
            EXECUTE FUNCTION update_schedule_from_lesson_teacher();
        ');

        // Триггер для таблицы lesson_schedule
        DB::unprepared('
            CREATE TRIGGER update_schedule_on_lesson_schedule_change
            AFTER INSERT OR UPDATE OR DELETE ON lesson_schedule
            FOR EACH ROW
            EXECUTE FUNCTION update_schedule_updated_at();
        ');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Удаляем триггеры
        DB::unprepared('DROP TRIGGER IF EXISTS update_schedule_on_lesson_change ON lessons');
        DB::unprepared('DROP TRIGGER IF EXISTS update_schedule_on_teacher_change ON lesson_teacher');
        DB::unprepared('DROP TRIGGER IF EXISTS update_schedule_on_lesson_schedule_change ON lesson_schedule');

        // Удаляем функции
        DB::unprepared('DROP FUNCTION IF EXISTS update_schedule_updated_at()');
        DB::unprepared('DROP FUNCTION IF EXISTS update_schedule_from_lesson_teacher()');
    }
}
