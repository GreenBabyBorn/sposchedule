<?php

namespace App\Http\Controllers;

use App\Http\Requests\Schedule\StoreScheduleRequest;
use App\Http\Requests\Schedule\UpdateScheduleRequest;
use App\Http\Resources\LessonResource;
use App\Http\Resources\ScheduleResource;
use App\Http\Resources\SkinnyGroup;
use App\Models\Schedule;
use Illuminate\Support\Carbon;
use Illuminate\Http\Request;
use App\Models\Group;
use App\Models\Lesson;
use App\Models\Semester;
use App\Http\Resources\SemesterResource;

class ScheduleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return ScheduleResource::collection(Schedule::all());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreScheduleRequest $request)
    {
        $data = $request->all();

        // if (isset($data['date'])) {
        //     // Преобразуем дату, используя Carbon
        //     $data['date'] = Carbon::createFromFormat('d.m.Y', $data['date'])->format('Y-m-d');
        // }
        // Создаем расписание с преобразованной датой
        $schedule = Schedule::create($data);
        return $schedule;
    }

    /**
     * Display the specified resource.
     */
    public function show(Schedule $schedule)
    {
        return new ScheduleResource($schedule);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateScheduleRequest $request, Schedule $schedule)
    {
        $schedule->update($request->all());
        return new ScheduleResource($schedule);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Schedule $schedule)
    {
        $schedule->delete();
        return response()->noContent();
    }
    public function fromMainToChangesSchedule(Request $request, Schedule $schedule)
    {
        $carbonDate = Carbon::parse($request->input('date'));
        $newSchedule = $schedule->replicate();
        $newSchedule->type = 'changes';
        $newSchedule->date = $carbonDate;
        $newSchedule->week_day = null;
        $newSchedule->save();

        foreach ($schedule->lessons as $lesson) {
            $newLesson = $lesson->replicate();
            $newLesson->schedule_id = $newSchedule->id; // Устанавливаем ID нового расписания

            $weekNumber =  Carbon::parse($newSchedule->semester->start)->diffInWeeks($request->input('date'));

            // Если неделя четная - ЧИСЛ, нечетная - ЗНАМ
            $weekType = ($weekNumber % 2 === 0) ? 'ЧИСЛ' : 'ЗНАМ';
            if($lesson->week_type === $weekType || $lesson->week_type === null) {
                $newLesson->save();
                $newLesson->teachers()->attach($lesson->teachers->pluck('id'));
            }

        }
        return new ScheduleResource($newSchedule);
    }
    public function getScheduleByDate(Request $request)
    {
        // Получаем дату из запроса
        $date = $request->input('date');

        // Преобразуем дату в объект Carbon
        $carbonDate = Carbon::parse($date);

        // Получаем день недели в формате, соответствующем полю week_day в расписании ('ПН', 'ВТ', ...)
        $weekDayMapping = [
            0 => 'ВС',
            1 => 'ПН',
            2 => 'ВТ',
            3 => 'СР',
            4 => 'ЧТ',
            5 => 'ПТ',
            6 => 'СБ',
        ];

        $weekDay = $weekDayMapping[$carbonDate->dayOfWeek];

        // Продолжаем с группами
        $groupsQuery = Group::query();
        $course = $request->input('course');
        if($course) {
            $groupsQuery->where('course', $course);
        }
        $groups = $groupsQuery->get();
        // Получаем все изменения расписания (type = 'changes') для выбранного дня недели
        $changes = Schedule::where('type', 'changes')
            ->where('date', $carbonDate)
            ->get();

        // Получаем все основные расписания (type = 'main') для выбранного дня недели
        $mainSchedules = Schedule::where('type', 'main')->where('published', true)
            ->where('week_day', $weekDay)->whereHas('semester', function ($query) use ($carbonDate) {
                $query->where('start', '<=', $carbonDate)
                    ->where('end', '>=', $carbonDate);
            })
            ->get();



        $semester = Semester::all()->where('start', '<=', $carbonDate)->where('end', '>=', $carbonDate)->first();
        if(!$semester) {
            return response()->json([
                'error' => 404,
                'message' => 'Семестра на данную дату не найдено'
            ], 404);
        }

        $semesterStart = Carbon::parse($semester->start);

        // Определяем номер недели с начала семестра
        $weekNumber = $semesterStart->diffInWeeks($date);

        // Если неделя четная - ЧИСЛ, нечетная - ЗНАМ
        $weekType = ($weekNumber % 2 === 0) ? 'ЧИСЛ' : 'ЗНАМ';
        // Массив для хранения финального расписания
        $finalSchedules = [
            'week_type' => $weekType,
            'schedules' => [],
        ];

        foreach ($groups as $group) {
            // Получаем изменения для текущей группы и текущего дня недели
            $groupChanges = $changes->where('group_id', $group->id);

            // Получаем основное расписание для текущей группы и текущего дня недели
            $groupMainSchedule = $mainSchedules->where('group_id', $group->id);

            // Создаем временное хранилище для расписания
            $groupSchedule = [];

            // Если основное расписание существует, подставляем его
            if ($groupMainSchedule->count() > 0) {
                foreach ($groupMainSchedule as $mainSchedule) {
                    $semesterStart = Carbon::parse($mainSchedule->semester->start);

                    // Определяем номер недели с начала семестра
                    $weekNumber = $semesterStart->diffInWeeks($date);

                    // Если неделя четная - ЧИСЛ, нечетная - ЗНАМ
                    $weekType = ($weekNumber % 2 === 0) ? 'ЧИСЛ' : 'ЗНАМ';
                    $groupSchedule = [
                        'id' => $mainSchedule->id,
                        'week_day' => $mainSchedule->week_day,
                        'type' => $mainSchedule->type,

                        // 'semester' => $mainSchedule->semester,
                        'lessons' => LessonResource::collection(Lesson::where('schedule_id', $mainSchedule->id)->where(function ($query) use ($weekType) {
                            $query->where('week_type', $weekType)
                                  ->orWhereNull('week_type');
                        })
                        ->orderBy('index')
                        ->get()),

                    ];
                }
            }

            // Теперь заменяем или дополняем слоты изменениями (changes)
            foreach ($groupChanges as $change) {
                $groupSchedule = [
                    'id' => $change->id,
                    'week_type' => $change->week_type,
                    'type' => $change->type,
                    'published' => $change->published,
                    // 'semester' => $change->semester,
                    'lessons' => LessonResource::collection(Lesson::where('schedule_id', $change->id)
                        ->orderBy('index')
                        ->get()),
                ];
            }

            // Добавляем финальное расписание группы в общий массив



            array_push($finalSchedules['schedules'], [

                'semester' => new SemesterResource($group->semesters->filter(function ($semester) use ($carbonDate) {
                    $start = Carbon::parse($semester->start);
                    $end = Carbon::parse($semester->end);

                    return $carbonDate->between($start, $end);
                })->first()),
                'group' => new SkinnyGroup($group),
                'schedule' => $groupSchedule
            ]);
            // $finalSchedules[$group->id] = [
            //     'group_name' => $group->name,
            //     'schedule' => $groupSchedule
            // ];
        }

        // Возвращаем финальное расписание
        return response()->json($finalSchedules);
    }

    public function getPublicSchedules(Request $request)
    {
        // Получаем дату из запроса
        $date = $request->input('date');

        // Преобразуем дату в объект Carbon
        $carbonDate = Carbon::parse($date);

        // Получаем день недели в формате, соответствующем полю week_day в расписании ('ПН', 'ВТ', ...)
        $weekDayMapping = [
            0 => 'ВС',
            1 => 'ПН',
            2 => 'ВТ',
            3 => 'СР',
            4 => 'ЧТ',
            5 => 'ПТ',
            6 => 'СБ',
        ];

        $weekDay = $weekDayMapping[$carbonDate->dayOfWeek];

        // $groups = Group::all();
        $groupsQuery = Group::query();
        $course = $request->input('course');
        if($course) {
            $groupsQuery->where('course', $course);
        }
        $groupName = $request->input('group');
        if($groupName) {
            $groupsQuery->where('name', 'LIKE', "%{$groupName}%");
        }
        $groups = $groupsQuery->get();


        // Получаем все изменения расписания (type = 'changes') для выбранного дня недели
        $changes = Schedule::where('type', 'changes')
            ->where('date', $carbonDate)
            ->where('published', true)
            ->get();

        // Получаем все основные расписания (type = 'main') для выбранного дня недели
        $mainSchedules = Schedule::where('type', 'main')->where('published', true)
            ->where('week_day', $weekDay)->whereHas('semester', function ($query) use ($carbonDate) {
                $query->where('start', '<=', $carbonDate)
                    ->where('end', '>=', $carbonDate);
            })
            ->get();



        $semester = Semester::all()->where('start', '<=', $carbonDate)->where('end', '>=', $carbonDate)->first();
        if(!$semester) {
            return response()->json([
                'error' => 404,
                'message' => 'Семестра на данную дату не найдено'
            ], 404);
        }

        $semesterStart = Carbon::parse($semester->start);

        // Определяем номер недели с начала семестра
        $weekNumber = $semesterStart->diffInWeeks($date);

        // Если неделя четная - ЧИСЛ, нечетная - ЗНАМ
        $weekType = ($weekNumber % 2 === 0) ? 'ЧИСЛ' : 'ЗНАМ';
        // Массив для хранения финального расписания
        $finalSchedules = [
            'week_type' => $weekType,
            'schedules' => [],
        ];

        foreach ($groups as $group) {
            // Получаем изменения для текущей группы и текущего дня недели
            $groupChanges = $changes->where('group_id', $group->id);

            // Получаем основное расписание для текущей группы и текущего дня недели
            $groupMainSchedule = $mainSchedules->where('group_id', $group->id);

            // Создаем временное хранилище для расписания
            $groupSchedule = [];

            // Если основное расписание существует, подставляем его
            if ($groupMainSchedule->count() > 0) {
                foreach ($groupMainSchedule as $mainSchedule) {
                    $semesterStart = Carbon::parse($mainSchedule->semester->start);

                    // Определяем номер недели с начала семестра
                    $weekNumber = $semesterStart->diffInWeeks($date);

                    // Если неделя четная - ЧИСЛ, нечетная - ЗНАМ
                    $weekType = ($weekNumber % 2 === 0) ? 'ЧИСЛ' : 'ЗНАМ';
                    $groupSchedule = [
                        'id' => $mainSchedule->id,
                        'week_day' => $mainSchedule->week_day,
                        'type' => $mainSchedule->type,

                        // 'semester' => $mainSchedule->semester,
                        'lessons' => LessonResource::collection(Lesson::where('schedule_id', $mainSchedule->id)->where(function ($query) use ($weekType) {
                            $query->where('week_type', $weekType)
                                  ->orWhereNull('week_type');
                        })
                        ->orderBy('index')
                        ->get()),

                    ];
                }
            }

            // Теперь заменяем или дополняем слоты изменениями (changes)
            foreach ($groupChanges as $change) {
                $groupSchedule = [
                    'id' => $change->id,
                    'week_type' => $change->week_type,
                    'type' => $change->type,
                    'published' => $change->published,
                    // 'semester' => $change->semester,
                    'lessons' => LessonResource::collection(Lesson::where('schedule_id', $change->id)
                        ->orderBy('index')
                        ->get()),
                ];
            }

            // Добавляем финальное расписание группы в общий массив



            array_push($finalSchedules['schedules'], [

                'semester' => new SemesterResource($group->semesters->filter(function ($semester) use ($carbonDate) {
                    $start = Carbon::parse($semester->start);
                    $end = Carbon::parse($semester->end);

                    return $carbonDate->between($start, $end);
                })->first()),
                'group' => new SkinnyGroup($group),
                'schedule' => $groupSchedule
            ]);

            usort($finalSchedules['schedules'], function ($a, $b) {
                // Если у объекта есть расписание, то он будет выше
                return !empty($b['schedule']) <=> !empty($a['schedule']);
            });
            // $finalSchedules[$group->id] = [
            //     'group_name' => $group->name,
            //     'schedule' => $groupSchedule
            // ];
        }

        // Возвращаем финальное расписание
        return response()->json($finalSchedules);
    }



}