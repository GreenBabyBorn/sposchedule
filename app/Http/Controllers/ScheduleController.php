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
use App\Http\Resources\SkinnyLessonResource;
use Illuminate\Support\Facades\Cache;
// use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

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
        //     // Получаем дату и преобразуем её в объект Carbon
        //     $date = $request->input('date');
        //     $carbonDate = Carbon::parse($date);

        //     // Получаем день недели в формате, соответствующем полю week_day в расписании ('ПН', 'ВТ', ...)
        //     $weekDayMapping = [
        //         0 => 'ВС',
        //         1 => 'ПН',
        //         2 => 'ВТ',
        //         3 => 'СР',
        //         4 => 'ЧТ',
        //         5 => 'ПТ',
        //         6 => 'СБ',
        //     ];

        //     $weekDay = $weekDayMapping[$carbonDate->dayOfWeek];

        //     // Получаем семестр для указанной даты
        //     $semester = DB::table('semesters')
        //         ->where('start', '<=', $carbonDate)
        //         ->where('end', '>=', $carbonDate)
        //         ->first();

        //     if (!$semester) {
        //         return response()->json([
        //             'error' => 404,
        //             'message' => 'Семестра на данную дату не найдено'
        //         ], 404);
        //     }

        //     // Рассчитываем номер недели и тип недели
        //     $semesterStart = Carbon::parse($semester->start);
        //     $weekNumber = $semesterStart->diffInWeeks($carbonDate);
        //     $weekType = ($weekNumber % 2 === 0) ? 'ЧИСЛ' : 'ЗНАМ';

        //     // Основной SQL-запрос
        //     $query = "
        //     SELECT
        //         g.id as group_id,
        //         g.name as group_name,
        //         s.id as schedule_id,
        //         s.type as schedule_type,
        //         s.week_day as schedule_week_day,
        //         s.date as schedule_date,
        //         s.published as schedule_published,
        //         l.id as lesson_id,
        //         l.index as lesson_index,
        //         l.week_type as lesson_week_type,
        //         l.cabinet as lesson_cabinet,
        //         l.building as lesson_building,
        //         l.message as lesson_message,
        //         subj.id as subject_id,
        //         subj.name as subject_name,
        //         subj.created_at as subject_created_at,
        //         subj.updated_at as subject_updated_at,
        //         json_agg(
        //             json_build_object(
        //                 'id', t.id,
        //                 'name', t.name,
        //                 'subjects', (
        //                     SELECT json_agg(
        //                         json_build_object(
        //                             'id', sub.id,
        //                             'name', sub.name
        //                         )
        //                     )
        //                     FROM subjects sub
        //                     JOIN subject_teacher st ON st.subject_id = sub.id
        //                     WHERE st.teacher_id = t.id
        //                 )
        //             )
        //         ) as teachers,
        //         sem.id as semester_id,
        //         sem.start as semester_start,
        //         sem.end as semester_end
        //     FROM groups g
        //     LEFT JOIN schedules s ON g.id = s.group_id
        //     LEFT JOIN lessons l ON s.id = l.schedule_id
        //     LEFT JOIN subjects subj ON l.subject_id = subj.id
        //     LEFT JOIN lesson_teacher lt ON l.id = lt.lesson_id
        //     LEFT JOIN teachers t ON lt.teacher_id = t.id
        //     LEFT JOIN semesters sem ON s.semester_id = sem.id
        //     WHERE s.published = true
        //     AND (
        //         (s.type = 'changes' AND s.date = :date)  -- приоритет для расписаний с изменениями
        //         OR (s.type = 'main' AND s.week_day = :week_day AND s.semester_id = :semester_id AND NOT EXISTS (
        //             SELECT 1
        //             FROM schedules changes
        //             WHERE changes.type = 'changes'
        //             AND changes.group_id = s.group_id
        //             AND changes.date = :date
        //         )) -- выводим основное расписание только если нет изменений
        //     )
        //     GROUP BY g.id, g.name, s.id, l.id, subj.id, sem.id
        //     ORDER BY s.type DESC, l.index
        // ";

        //     // Массив параметров для SQL-запроса
        //     $params = [
        //         'date' => $carbonDate->toDateString(),
        //         'week_day' => $weekDay,
        //         'semester_id' => $semester->id
        //     ];


        //     // Выполняем SQL-запрос
        //     $results = DB::select($query, $params);

        //     // Преобразуем результаты запроса в необходимую структуру данных
        //     $finalSchedules = [
        //         'week_type' => $weekType,
        //         'schedules' => []
        //     ];

        //     // Группируем результаты по группам и расписаниям
        //     foreach ($results as $row) {
        //         $groupId = $row->group_id;
        //         $scheduleId = $row->schedule_id;

        //         // Если группа еще не добавлена в финальный массив, добавляем её
        //         if (!isset($finalSchedules['schedules'][$groupId])) {
        //             $finalSchedules['schedules'][$groupId] = [
        //                 'semester' => [
        //                     'id' => $row->semester_id,
        //                     'start' => $row->semester_start,
        //                     'end' => $row->semester_end
        //                 ],
        //                 'group' => [
        //                     'id' => $row->group_id,
        //                     'name' => $row->group_name
        //                 ],
        //                 'schedule' => [
        //                     'id' => $row->schedule_id,
        //                     'week_day' => $row->schedule_week_day,
        //                     'type' => $row->schedule_type,
        //                     'published' => $row->schedule_published,
        //                     'lessons' => []
        //                 ],

        //             ];
        //         }

        //         // Добавляем уроки в расписание группы, если тип недели совпадает или не указан (общее для всех недель)
        //         if ($row->lesson_week_type === $weekType || $row->lesson_week_type === null) {
        //             $finalSchedules['schedules'][$groupId]['schedule']['lessons'][] = [
        //                 'id' => $row->lesson_id,
        //                 'schedule_id' => $row->schedule_id,
        //                 'index' => $row->lesson_index,
        //                 'cabinet' => $row->lesson_cabinet,
        //                 'subject' => [
        //                     'id' => $row->subject_id,
        //                     'name' => $row->subject_name,
        //                     'created_at' => Carbon::parse($row->subject_created_at)->format('Y-m-d\TH:i:s.u\Z'),
        //                     'updated_at' => Carbon::parse($row->subject_updated_at)->format('Y-m-d\TH:i:s.u\Z')
        //                 ],
        //                 'teachers' => json_decode($row->teachers, true),
        //                 'building' => $row->lesson_building,
        //                 'week_type' => $row->lesson_week_type,
        //                 'message' => $row->lesson_message,
        //             ];
        //         }
        //     }

        //     // Преобразуем результат в плоский массив
        //     $finalSchedules['schedules'] = array_values($finalSchedules['schedules']);

        //     // Возвращаем финальное расписание
        //     return response()->json($finalSchedules);










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
        $building = $request->input('building');
        if($building) {
            $groupsQuery->whereHas('buildings', function ($query) use ($building) {
                $query->where('name', $building);
            });
        }
        $name = $request->input('group');
        if($name) {
            // $groupsQuery->where('name', $name);
            $groupsQuery->where('name', 'LIKE', "%{$name}%");
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

                'semester' => $group->semesters->filter(function ($semester) use ($carbonDate) {
                    $start = Carbon::parse($semester->start);
                    $end = Carbon::parse($semester->end);

                    return $carbonDate->between($start, $end);
                })->first() ? new SemesterResource(
                    $group->semesters->filter(function ($semester) use ($carbonDate) {
                        $start = Carbon::parse($semester->start);
                        $end = Carbon::parse($semester->end);
                        return $carbonDate->between($start, $end);
                    })->first()
                ) : null, // Возвращаем null, если семестр не найден
                'group' => new SkinnyGroup($group),
                'schedule' => $groupSchedule
            ]);
            // $finalSchedules[$group->id] = [
            //     'group_name' => $group->name,
            //     'schedule' => $groupSchedule
            // ];
        }

        // usort($finalSchedules['schedules'], function ($a, $b) {
        //     if($a['schedule']['type'] === 'changes' && $b['schedule']['type'] !== 'changes') {
        //         return -1;
        //     } elseif($a['schedule']['type'] !== 'changes' && $b['schedule']['type'] === 'changes') {
        //         return 1;
        //     } else {
        //         return 0;
        //     }
        // });

        // Возвращаем финальное расписание
        return response()->json($finalSchedules);
    }


    public function getScheduleByDatePrint(Request $request)
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
        if ($course) {
            $groupsQuery->where('course', $course);
        }
        $groups = $groupsQuery->get();

        // Получаем все изменения расписания (type = 'changes') для выбранного дня недели
        $changes = Schedule::where('type', 'changes')
            ->where('date', $carbonDate)
            ->get();

        // Определяем семестр
        $semester = Semester::where('start', '<=', $carbonDate)
            ->where('end', '>=', $carbonDate)
            ->first();

        if (!$semester) {
            return response()->json([
                'error' => 404,
                'message' => 'Семестра на данную дату не найдено'
            ], 404);
        }

        // Определяем номер недели с начала семестра
        $semesterStart = Carbon::parse($semester->start);
        $weekNumber = $semesterStart->diffInWeeks($carbonDate);

        // Если неделя четная - ЧИСЛ, нечетная - ЗНАМ
        $weekType = ($weekNumber % 2 === 0) ? 'ЧИСЛ' : 'ЗНАМ';

        // Массив для хранения финального расписания, сгруппированного по зданиям
        $finalSchedules = [
            '1-5' => [
                'week_type' => $weekType,
                'schedules' => []
            ],
            '6' => [
                'week_type' => $weekType,
                'schedules' => []
            ],
        ];

        foreach ($groups as $group) {
            // Получаем изменения для текущей группы и текущего дня недели
            $groupChanges = $changes->where('group_id', $group->id);

            // Создаем временное хранилище для расписания
            $groupSchedule = [];

            // Заменяем или дополняем слоты изменениями (changes)
            foreach ($groupChanges as $change) {
                if ($change->published) {
                    $groupSchedule = [
                        'id' => $change->id,
                        'week_type' => $change->week_type,
                        'lessons' => LessonResource::collection(Lesson::where('schedule_id', $change->id)
                            ->orderBy('index')
                            ->get()),
                    ];
                }
            }

            // Получаем все здания, связанные с текущей группой
            $buildings = $group->buildings;

            foreach ($buildings as $building) {
                // Группируем расписания по зданию (building)
                $buildingKey = $building->name == '6' ? '6' : '1-5';

                // Проверяем, существует ли семестр
                $filteredSemester = $group->semesters->filter(function ($semester) use ($carbonDate) {
                    $start = Carbon::parse($semester->start);
                    $end = Carbon::parse($semester->end);
                    return $carbonDate->between($start, $end);
                })->first();

                // Если семестр найден, передаем его в SemesterResource, если нет — передаем null или другое значение
                array_push($finalSchedules[$buildingKey]['schedules'], [
                    'semester' => $filteredSemester ? new SemesterResource($filteredSemester) : null, // Проверка на null
                    'group' => new SkinnyGroup($group),
                    'schedule' => $groupSchedule,
                ]);
            }
        }


        // Возвращаем финальное расписание
        return response()->json($finalSchedules);
    }

    // public function getPublicSchedules(Request $request)
    // {
    //     // Получаем дату из запроса и преобразуем её в объект Carbon
    //     $date = $request->input('date');
    //     $carbonDate = Carbon::parse($date);

    //     // Получаем день недели
    //     $weekDayMapping = [
    //         0 => 'ВС',
    //         1 => 'ПН',
    //         2 => 'ВТ',
    //         3 => 'СР',
    //         4 => 'ЧТ',
    //         5 => 'ПТ',
    //         6 => 'СБ',
    //     ];
    //     $weekDay = $weekDayMapping[$carbonDate->dayOfWeek];

    //     // Формируем запрос на получение групп с учётом фильтров
    //     $groupsQuery = Group::query();
    //     if ($building = $request->input('building')) {
    //         $groupsQuery->where('building', $building);
    //     }
    //     if ($course = $request->input('course')) {
    //         $groupsQuery->where('course', $course);
    //     }
    //     if ($groupName = $request->input('group')) {
    //         $groupsQuery->where('name', 'LIKE', "%{$groupName}%");
    //     }
    //     $groups = $groupsQuery->get();

    //     // Получаем семестр, соответствующий указанной дате
    //     $semester = Semester::where('start', '<=', $carbonDate)
    //             ->where('end', '>=', $carbonDate)
    //             ->first();


    //     if (!$semester) {
    //         return response()->json([
    //             'error' => 404,
    //             'message' => 'Семестра на данную дату не найдено'
    //         ], 404);
    //     }

    //     // Преобразуем поле 'start' в Carbon, если это строка
    //     $semesterStart = Carbon::parse($semester->start);

    //     // Рассчитываем номер недели с начала семестра и тип недели
    //     $weekNumber = $semesterStart->diffInWeeks($carbonDate);
    //     $weekType = ($weekNumber % 2 === 0) ? 'ЧИСЛ' : 'ЗНАМ';

    //     // Заранее загружаем все изменения и основные расписания для текущей даты
    //     $schedules = Schedule::where('published', true)
    //         ->where(function ($query) use ($carbonDate, $weekDay) {
    //             $query->where('type', 'changes')
    //                   ->where('date', $carbonDate)
    //                   ->orWhere(function ($q) use ($weekDay) {
    //                       $q->where('type', 'main')
    //                         ->where('week_day', $weekDay);
    //                   });
    //         })
    //         ->with(['lessons' => function ($query) use ($weekType) {
    //             $query->where(function ($q) use ($weekType) {
    //                 $q->where('week_type', $weekType)
    //                   ->orWhereNull('week_type');
    //             });
    //         }])
    //         ->get();

    //     $finalSchedules = [
    //         'week_type' => $weekType,
    //         'schedules' => [],
    //     ];

    //     foreach ($groups as $group) {
    //         // Фильтруем изменения и основные расписания для текущей группы
    //         $groupSchedules = $schedules->where('group_id', $group->id);

    //         // Предпочтение отдаётся изменениям, если они есть
    //         $groupSchedule = $groupSchedules->where('type', 'changes')->first()
    //                         ?: $groupSchedules->where('type', 'main')->first();

    //         // Добавляем расписание группы в общий массив
    //         $finalSchedules['schedules'][] = [
    //             'group_name' => $group->name,
    //             'schedule' => $groupSchedule ? [
    //                 // 'id' => $groupSchedule->id,
    //                 'week_day' => $groupSchedule->week_day,
    //                 'type' => $groupSchedule->type,
    //                 'lessons' => SkinnyLessonResource::collection($groupSchedule->lessons)
    //             ] : null
    //         ];
    //     }

    //     // Сортируем расписания, у которых есть данные, выше пустых
    //     usort($finalSchedules['schedules'], function ($a, $b) {
    //         return !empty($b['schedule']) <=> !empty($a['schedule']);
    //     });

    //     return response()->json($finalSchedules);
    // }
    // public function getPublicSchedules(Request $request)
    // {
    //     // Получаем дату и преобразуем её в объект Carbon
    //     $date = $request->input('date');
    //     $carbonDate = Carbon::parse($date);

    //     // Получаем день недели
    //     $weekDayMapping = [
    //         0 => 'ВС',
    //         1 => 'ПН',
    //         2 => 'ВТ',
    //         3 => 'СР',
    //         4 => 'ЧТ',
    //         5 => 'ПТ',
    //         6 => 'СБ',
    //     ];
    //     $weekDay = $weekDayMapping[$carbonDate->dayOfWeek];

    //     // Получаем семестр для указанной даты
    //     $semester = DB::table('semesters')
    //         ->where('start', '<=', $carbonDate)
    //         ->where('end', '>=', $carbonDate)
    //         ->first();

    //     if (!$semester) {
    //         return response()->json([
    //             'error' => 404,
    //             'message' => 'Семестра на данную дату не найдено'
    //         ], 404);
    //     }

    //     // Рассчитываем номер недели и тип недели
    //     $semesterStart = Carbon::parse($semester->start);
    //     $weekNumber = $semesterStart->diffInWeeks($carbonDate);
    //     $weekType = ($weekNumber % 2 === 0) ? 'ЧИСЛ' : 'ЗНАМ';

    //     // Основной SQL-запрос
    //     $query = "
    //         SELECT g.name as group_name,
    //                s.id as schedule_id, s.week_day, s.type,
    //                json_agg(
    //                    json_build_object(
    //                        'id', l.id,
    //                        'index', l.index,
    //                        'subject_name', subj.name,
    //                        'week_type', l.week_type,
    //                        'cabinet', l.cabinet,
    //                        'building', l.building,
    //                        'teachers', (
    //                            SELECT json_agg(
    //                                json_build_object(
    //                                    'id', t.id,
    //                                    'name', t.name
    //                                )
    //                            )
    //                            FROM lesson_teacher lt
    //                            JOIN teachers t ON lt.teacher_id = t.id
    //                            WHERE lt.lesson_id = l.id
    //                        )
    //                    )
    //                ) as lessons
    //         FROM groups g
    //         LEFT JOIN schedules s ON g.id = s.group_id
    //         LEFT JOIN lessons l ON s.id = l.schedule_id
    //         LEFT JOIN subjects subj ON l.subject_id = subj.id
    //         WHERE s.published = true
    //         AND (
    //             (s.type = 'changes' AND s.date = :date)
    //             OR (s.type = 'main' AND s.week_day = :week_day AND s.semester_id = :semester_id)
    //         )
    //     ";

    //     // Массив параметров для SQL-запроса
    //     $params = [
    //         'date' => $carbonDate->toDateString(),
    //         'week_day' => $weekDay,
    //         'semester_id' => $semester->id,
    //     ];

    //     // Добавляем фильтры, если они присутствуют
    //     if ($building = $request->input('building')) {
    //         $query .= " AND g.building = :building ";
    //         $params['building'] = $building;
    //     }
    //     if ($course = $request->input('course')) {
    //         $query .= " AND g.course = :course ";
    //         $params['course'] = $course;
    //     }
    //     if ($groupName = $request->input('group')) {
    //         $query .= " AND g.name LIKE :group_name ";
    //         $params['group_name'] = "%{$groupName}%";
    //     }

    //     // Завершаем запрос
    //     $query .= "GROUP BY g.name, s.id ORDER BY s.type DESC";

    //     // Выполняем SQL-запрос
    //     $schedules = DB::select($query, $params);

    //     // Преобразуем JSON в массив и возвращаем результат
    //     $finalSchedules = [
    //         'week_type' => $weekType,
    //         'schedules' => []
    //     ];

    //     foreach ($schedules as $schedule) {
    //         $schedule->lessons = json_decode($schedule->lessons, true);
    //         usort($schedule->lessons, function ($a, $b) {
    //             return $a['index'] <=> $b['index'];
    //         });
    //         // Добавляем расписание в результат
    //         $finalSchedules['schedules'][] = [
    //             'group_name' => $schedule->group_name,
    //             'schedule' => [
    //                 'week_day' => $schedule->week_day,
    //                 'type' => $schedule->type,
    //                 'lessons' => $schedule->lessons
    //             ]
    //         ];
    //     }

    //     // Сортировка расписаний с данными выше пустых
    //     usort($finalSchedules['schedules'], function ($a, $b) {
    //         return !empty($b['schedule']) <=> !empty($a['schedule']);
    //     });

    //     // Возвращаем результат
    //     return response()->json($finalSchedules);
    // }
    public function getPublicSchedules(Request $request)
    {
        // Получаем дату и преобразуем её в объект Carbon
        $date = $request->input('date');
        $carbonDate = Carbon::parse($date);

        // Получаем день недели
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

        // Получаем семестр для указанной даты
        $semester = DB::table('semesters')
            ->where('start', '<=', $carbonDate)
            ->where('end', '>=', $carbonDate)
            ->first();

        if (!$semester) {
            return response()->json([
                'error' => 404,
                'message' => 'Семестра на данную дату не найдено'
            ], 404);
        }

        // Рассчитываем номер недели и тип недели
        $semesterStart = Carbon::parse($semester->start);
        $weekNumber = $semesterStart->diffInWeeks($carbonDate);
        $weekType = ($weekNumber % 2 === 0) ? 'ЧИСЛ' : 'ЗНАМ';

        // Основной SQL-запрос
        $query = "
    SELECT g.name as group_name, 
           s.id as schedule_id, s.week_day, s.type, s.updated_at,
           json_agg(
               json_build_object(
                   'id', l.id, 
                   'index', l.index, 
                   'subject_name', subj.name, 
                   'week_type', l.week_type, 
                   'cabinet', l.cabinet,
                   'building', l.building,
                   'message', l.message,
                   'teachers', (
                       SELECT json_agg(
                           json_build_object(
                               'id', t.id, 
                               'name', t.name
                           )
                       )
                       FROM lesson_teacher lt
                       JOIN teachers t ON lt.teacher_id = t.id
                       WHERE lt.lesson_id = l.id
                   )
               )
           ) as lessons
    FROM groups g
    LEFT JOIN schedules s ON g.id = s.group_id
    LEFT JOIN lessons l ON s.id = l.schedule_id
    LEFT JOIN subjects subj ON l.subject_id = subj.id
    WHERE s.published = true
    AND (
        (s.type = 'changes' AND s.date = :date)
        OR (s.type = 'main' AND s.week_day = :week_day AND s.semester_id = :semester_id)
    )
";

        // Массив параметров для SQL-запроса
        $params = [
            'date' => $carbonDate->toDateString(),
            'week_day' => $weekDay,
            'semester_id' => $semester->id,
        ];

        // Добавляем фильтры, если они присутствуют
        if ($building = $request->input('building')) {
            $query .= "
                AND EXISTS (
                    SELECT 1 
                    FROM group_building gb
                    JOIN buildings b ON gb.building_name = b.name
                    WHERE gb.group_id = g.id 
                    AND b.name = :building
                )
            ";
            $params['building'] = $building;
        }
        if ($course = $request->input('course')) {
            $query .= " AND g.course = :course ";
            $params['course'] = $course;
        }
        if ($groupName = $request->input('group')) {
            $query .= " AND g.name LIKE :group_name ";
            $params['group_name'] = "%{$groupName}%";
        }
        if ($cabinet = $request->input('cabinet')) {
            // Условие для фильтрации по кабинету
            $query .= " AND l.cabinet LIKE :cabinet ";
            $params['cabinet'] = "%{$cabinet}%"; // добавляем параметр для поиска по кабинету
        }
        // Добавляем фильтрацию по преподавателю, если параметр передан
        if ($teacher = $request->input('teacher')) {
            $query .= "
        AND EXISTS (
            SELECT 1 FROM lesson_teacher lt
            JOIN teachers t ON lt.teacher_id = t.id
            WHERE lt.lesson_id = l.id
            AND t.name ILIKE :teacher_name
        )
    ";
            $params['teacher_name'] = "%{$teacher}%";
        }

        // Завершаем запрос
        $query .= "GROUP BY g.name, s.id ORDER BY s.type DESC";

        // Выполняем SQL-запрос
        $schedules = DB::select($query, $params);

        // Преобразуем JSON в массив и возвращаем результат
        $finalSchedules = [
            'week_type' => $weekType,
            'last_updated' => null, // инициализируем
            'schedules' => []
        ];

        // Массив для отслеживания выбранного расписания для каждой группы
        $groupSchedules = [];

        foreach ($schedules as $schedule) {
            $schedule->lessons = json_decode($schedule->lessons, true);

            // Фильтруем пары по текущему типу недели (ЧИСЛ или ЗНАМ)
            $filteredLessons = [];
            foreach ($schedule->lessons as $lesson) {
                // Если тип недели совпадает с текущим или занятие проводится каждую неделю
                if ($lesson['week_type'] === $weekType || $lesson['week_type'] === null) {
                    $filteredLessons[] = $lesson;
                }
            }

            if (empty($filteredLessons)) {
                continue;
            }
            // Сортируем занятия по индексу
            usort($filteredLessons, function ($a, $b) {
                return $a['index'] <=> $b['index'];
            });

            // Обновляем last_updated
            if (is_null($finalSchedules['last_updated']) || $schedule->updated_at > $finalSchedules['last_updated']) {
                $finalSchedules['last_updated'] = $schedule->updated_at;
            }

            // Если для группы еще нет расписания или текущее расписание имеет тип 'changes', заменяем
            if (!isset($groupSchedules[$schedule->group_name]) || $schedule->type === 'changes') {
                $groupSchedules[$schedule->group_name] = [
                    'group_name' => $schedule->group_name,
                    'schedule' => [
                        'week_day' => $schedule->week_day,
                        'type' => $schedule->type,
                        'lessons' => $filteredLessons // Используем отфильтрованные занятия
                    ]
                ];
            }
        }

        // Добавляем расписания для каждой группы в финальный массив
        $finalSchedules['schedules'] = array_values($groupSchedules);

        // Сортировка расписаний с данными выше пустых
        usort($finalSchedules['schedules'], function ($a, $b) {
            return !empty($b['schedule']) <=> !empty($a['schedule']);
        });

        // Возвращаем результат
        return response()->json($finalSchedules);
    }

}
