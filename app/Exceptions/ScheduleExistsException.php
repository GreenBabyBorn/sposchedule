<?php

namespace App\Exceptions;

use Exception;

class ScheduleExistsException extends Exception
{
    public $scheduleId;

    public function __construct($scheduleId)
    {
        $this->scheduleId = $scheduleId;
    }

    public function render()
    {
        return response()->json([
            'message' => 'Для данной группы уже существует расписание с таким типом недели и днем недели.',
            'schedule_id' => $this->scheduleId,
        ], 422);
    }
}
