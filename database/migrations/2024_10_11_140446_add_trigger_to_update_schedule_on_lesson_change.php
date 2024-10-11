Б<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTriggerToUpdateScheduleOnLessonChange extends Migration
{
    public function up()
    {
        // Создаем функцию для обновления поля updated_at у schedule
        DB::unprepared('
CREATE OR REPLACE FUNCTION update_schedule_updated_at()
RETURNS TRIGGER AS $$
BEGIN
-- Обновляем расписание по schedule_id, если оно есть
IF TG_OP = \'INSERT\' OR TG_OP = \'UPDATE\' THEN
IF NEW.schedule_id IS NOT NULL THEN
UPDATE schedules
SET updated_at = NOW()
WHERE id = NEW.schedule_id;
END IF;
ELSIF TG_OP = \'DELETE\' THEN
IF OLD.schedule_id IS NOT NULL THEN
UPDATE schedules
SET updated_at = NOW()
WHERE id = OLD.schedule_id;
END IF;
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

        // Триггер для таблицы lesson_schedule
        DB::unprepared('
CREATE TRIGGER update_schedule_on_lesson_schedule_change
AFTER INSERT OR UPDATE OR DELETE ON lesson_schedule
FOR EACH ROW
EXECUTE FUNCTION update_schedule_updated_at();
');
    }

    public function down()
    {
        // Удаляем триггеры
        DB::unprepared('DROP TRIGGER IF EXISTS update_schedule_on_lesson_change ON lessons');
        DB::unprepared('DROP TRIGGER IF EXISTS update_schedule_on_lesson_schedule_change ON lesson_schedule');

        // Удаляем функцию
        DB::unprepared('DROP FUNCTION IF EXISTS update_schedule_updated_at()');
    }
}
