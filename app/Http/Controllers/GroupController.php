<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\Group\StoreGroupRequest;
use App\Http\Requests\Group\UpdateGroupRequest;
use App\Http\Requests\Group\AttachSemesterRequest;
use App\Http\Requests\Group\DetachSemesterRequest;
use App\Http\Resources\GroupResource;
use App\Http\Resources\LessonResource;
use App\Http\Resources\ScheduleResource;
use App\Models\Group;
use App\Models\Schedule;
use App\Models\Semester;

class GroupController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return GroupResource::collection(Group::all());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreGroupRequest $request)
    {
        $name = $request->name;

        $group = Group::create([
            ...$request->all(),
            "name" => $name,

        ]);
        $semesterIds = array_column($request->semesters, 'id');
        $group->semesters()->sync($semesterIds);
        $group->refresh();
        return new GroupResource($group);
    }

    /**
     * Display the specified resource.
     */
    public function show(Group $group)
    {
        return new GroupResource($group);
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateGroupRequest $request, Group $group)
    {
        $group->update($request->all());
        $group->refresh();
        return new GroupResource($group);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Group $group)
    {
        $group->delete();
        return response()->noContent();
    }

    /**
     * Attach a semester to a group
     */
    public function attachSemester(AttachSemesterRequest $request, Group $group)
    {
        $semester = Semester::findOrFail($request->semester_id);
        $group->semesters()->attach($semester);
        return response()->json(['message' => 'Семестр успешно добавлен к группе.', "semester" => $semester]);
    }

    /**
     * Detach a semester from a group
     */
    public function detachSemester(DetachSemesterRequest $request, Group $group)
    {
        $semester = Semester::findOrFail($request->semester_id);
        $group->semesters()->detach($semester);
        return response()->json(['message' => 'Семестр отвязан от группы.', "semester" => $semester ]);
    }

    public function scheduleMain(Group $group, Semester $semester)
    {
        // Получаем расписания для данной группы с типом 'main'
        $schedules = Schedule::where('group_id', $group->id)
        ->where('semester_id', $semester->id)
        ->where('type', 'main')
        ->with('lessons') // Загрузить уроки вместе с расписанием
        ->get()
        ->groupBy('week_day');


        // Инициализируем пустой массив для ответа
        $response = [
        'ПН' => [],
        'ВТ' => [],
        'СР' => [],
        'ЧТ' => [],
        'ПТ' => [],
        'СБ' => [],
        'ВС' => [],
        ];
        // Заполняем ответ расписаниями по дням недели

        // Возвращаем ответ в формате JSON
        foreach ($schedules as $weekDay => $scheduleGroup) {
            // Подготавливаем массив для дня недели
            $dayLessons = [];

            // Сортируем расписание по времени начала урока
            foreach ($scheduleGroup as $schedule) {
                foreach ($schedule->lessons as $lesson) {
                    $index = $lesson->index; // Поле "index", указывающее номер пары

                    if (!isset($dayLessons[$index])) {
                        $dayLessons[$index] = [
                            'index' => $index,
                            'schedule_id' => $schedule->id,
                            // 'ЧИСЛ' => new \stdClass() ,
                            // 'ЗНАМ' => new \stdClass() ,
                        ];
                    }
                    if ($lesson->week_type === 'ЧИСЛ' && !empty($lesson)) {
                        $dayLessons[$index]['ЧИСЛ'] = new LessonResource($lesson);
                        if (empty($dayLessons[$index]['ЗНАМ'])) {
                            $dayLessons[$index]['ЗНАМ'] = new \stdClass();
                        }
                    } if ($lesson->week_type === 'ЗНАМ' && !empty($lesson)) {
                        $dayLessons[$index]['ЗНАМ'] = new LessonResource($lesson);
                        if (empty($dayLessons[$index]['ЧИСЛ'])) {
                            $dayLessons[$index]['ЧИСЛ'] = new \stdClass();
                        }
                        // $dayLessons[$index]['ЧИСЛ'] = new \stdClass();
                    }
                    if($lesson->week_type === null) {
                        $dayLessons[$index]['lesson'] = new LessonResource($lesson);

                    }
                }
            }
            ksort($dayLessons);
            // Преобразуем $dayLessons в плоский массив и добавляем его в ответ
            $response[$weekDay] = array_values($dayLessons);
        }

        return response()->json($response);

    }
}
