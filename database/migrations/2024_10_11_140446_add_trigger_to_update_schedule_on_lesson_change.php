<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class() extends Migration
{
    public function up()
    {
        // Создаем функцию для обновления поля updated_at у schedules
        DB::unprepared('
        CREATE OR REPLACE FUNCTION update_schedule_updated_at()
        RETURNS TRIGGER AS $$
        DECLARE
            current_schedule_id BIGINT;
        BEGIN
            -- Проверяем, если операция была на lessons
            IF TG_TABLE_NAME = \'lessons\' THEN
                current_schedule_id := NEW.schedule_id; -- Сохраняем schedule_id из NEW записи
            ELSIF TG_TABLE_NAME = \'lesson_teacher\' THEN
                -- Получаем schedule_id из lessons, когда срабатывает триггер на lesson_teacher
                SELECT schedule_id INTO current_schedule_id FROM lessons WHERE id = NEW.lesson_id;
            END IF;
        
            -- Обновляем расписание по schedule_id, если оно есть
            IF current_schedule_id IS NOT NULL THEN
                UPDATE schedules
                SET updated_at = NOW()
                WHERE id = current_schedule_id;
            END IF;
        
            RETURN NEW;
        END;
        $$ LANGUAGE plpgsql;
        ');

        // Удаляем триггер, если он существует
        DB::unprepared('DROP TRIGGER IF EXISTS update_schedule_on_lesson_change ON lessons');

        // Создаем триггер для таблицы lessons
        DB::unprepared('
            CREATE TRIGGER update_schedule_on_lesson_change
            AFTER INSERT OR UPDATE OR DELETE ON lessons
            FOR EACH ROW
            EXECUTE FUNCTION update_schedule_updated_at();
        ');

        // Удаляем триггер, если он существует
        DB::unprepared('DROP TRIGGER IF EXISTS update_schedule_on_lesson_schedule_change ON lesson_schedule');

        // Создаем триггер для таблицы lesson_schedule
        DB::unprepared('
            CREATE TRIGGER update_schedule_on_lesson_schedule_change
            AFTER INSERT OR UPDATE OR DELETE ON lesson_schedule
            FOR EACH ROW
            EXECUTE FUNCTION update_schedule_updated_at();
        ');

        // Удаляем триггер, если он существует
        DB::unprepared('DROP TRIGGER IF EXISTS update_schedule_on_teacher_change ON lesson_teacher');

        // Создаем триггер для таблицы lesson_teacher
        DB::unprepared('
            CREATE TRIGGER update_schedule_on_teacher_change
            AFTER INSERT OR UPDATE OR DELETE ON lesson_teacher
            FOR EACH ROW
            EXECUTE FUNCTION update_schedule_updated_at();
        ');
    }

    public function down()
    {
        // Удаляем триггеры
        DB::unprepared('DROP TRIGGER IF EXISTS update_schedule_on_lesson_change ON lessons');
        DB::unprepared('DROP TRIGGER IF EXISTS update_schedule_on_lesson_schedule_change ON lesson_schedule');
        DB::unprepared('DROP TRIGGER IF EXISTS update_schedule_on_teacher_change ON lesson_teacher');

        // Удаляем функцию
        DB::unprepared('DROP FUNCTION IF EXISTS update_schedule_updated_at()');
    }
};
