<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class () extends Migration {
    public function up()
    {
        // Удаляем триггеры и функции
        DB::unprepared('DROP TRIGGER IF EXISTS update_schedule_on_lesson_change ON lessons');
        DB::unprepared('DROP TRIGGER IF EXISTS update_schedule_on_teacher_change ON lesson_teacher');
        DB::unprepared('DROP TRIGGER IF EXISTS update_schedule_on_lesson_schedule_change ON lesson_schedule');

        DB::unprepared('DROP FUNCTION IF EXISTS update_schedule_updated_at');
    }

    public function down()
    {
        // Восстанавливаем триггеры и функции (если нужно)
        DB::unprepared('
        CREATE OR REPLACE FUNCTION update_schedule_updated_at()
        RETURNS TRIGGER AS $$
        DECLARE
            current_schedule_id BIGINT;
        BEGIN
            IF TG_TABLE_NAME = \'lessons\' THEN
                current_schedule_id := NEW.schedule_id;
            ELSIF TG_TABLE_NAME = \'lesson_teacher\' THEN
                SELECT schedule_id INTO current_schedule_id FROM lessons WHERE id = NEW.lesson_id;
            END IF;

            IF current_schedule_id IS NOT NULL THEN
                UPDATE schedules
                SET updated_at = NOW()
                WHERE id = current_schedule_id;
            END IF;

            RETURN NEW;
        END;
        $$ LANGUAGE plpgsql;
        ');

        // Восстанавливаем триггеры
        DB::unprepared('
            CREATE TRIGGER update_schedule_on_lesson_change
            AFTER INSERT OR UPDATE OR DELETE ON lessons
            FOR EACH ROW
            EXECUTE FUNCTION update_schedule_updated_at();
        ');

        DB::unprepared('
            CREATE TRIGGER update_schedule_on_teacher_change
            AFTER INSERT OR UPDATE OR DELETE ON lesson_teacher
            FOR EACH ROW
            EXECUTE FUNCTION update_schedule_updated_at();
        ');

        DB::unprepared('
            CREATE TRIGGER update_schedule_on_lesson_schedule_change
            AFTER INSERT OR UPDATE OR DELETE ON lesson_schedule
            FOR EACH ROW
            EXECUTE FUNCTION update_schedule_updated_at();
        ');
    }
};
