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
use App\Facades\HistoryLogger;
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
        // Создаем расписание с преобразованной датой
        $schedule = Schedule::create($data);
        HistoryLogger::logAction('Добавлено расписание', $schedule->toArray());
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
        HistoryLogger::logAction('Обновлено расписание', $schedule->toArray());
        return new ScheduleResource($schedule);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Schedule $schedule)
    {
        HistoryLogger::logAction('Удалено расписание', $schedule->toArray());
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


            if($group->semesters->filter(function ($semester) use ($carbonDate) {
                $start = Carbon::parse($semester->start);
                $end = Carbon::parse($semester->end);

                return $carbonDate->between($start, $end);
            })->first()) {
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
            }

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
            $query .= " AND l.cabinet ILIKE :cabinet ";
            $params['cabinet'] = "%{$cabinet}%";
        }

        if ($subject = $request->input('subject')) {
            $query .= " AND subj.name ILIKE :subject ";
            $params['subject'] = "%{$subject}%";
        }

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

            // Если нет изменений или расписаний, пропускаем текущую группу
            if (empty($groupSchedule)) {
                continue;
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

        // Фильтруем пустые расписания, чтобы выводить только те здания, в которых есть расписания
        $finalSchedules = array_filter($finalSchedules, function ($buildingSchedule) {
            return !empty($buildingSchedule['schedules']);
        });

        // Возвращаем финальное расписание
        return response()->json($finalSchedules);
    }


    public function getSchedulesMainPrint(Semester $semester, Request $request)
    {
        // Получаем курс и диапазон корпусов из query параметров
        $course = $request->query('course');
        $buildingsFilter = $request->query('buildings', null); // Ожидается строка или массив

        // Если курс указан, фильтруем группы по этому курсу, иначе получаем все группы
        $groupsQuery = Group::query();
        if ($course) {
            // Убедимся, что курс передан как целое число
            $groupsQuery->where('course', intval($course));
        }

        // Загружаем группы с их связанными зданиями
        $groups = $groupsQuery->with('buildings')->get();

        // Преобразуем фильтр зданий в массив, если он был передан
        $buildingsFilterArray = null;
        if ($buildingsFilter) {
            // Разделяем строку по запятым и преобразуем в массив
            $buildingsFilterArray = array_map('trim', explode(',', $buildingsFilter));
        }

        // Инициализируем пустой массив для ответа
        $response = [];

        foreach ($groups as $group) {
            // Получаем расписания для данной группы с типом 'main'
            $schedules = Schedule::where('group_id', $group->id)
                ->where('semester_id', $semester->id)
                ->where('type', 'main')
                ->where('published', true)
                ->with('lessons') // Загрузить уроки вместе с расписанием
                ->get()
                ->groupBy('week_day');

            // Инициализируем пустой массив для расписания этой группы по дням недели
            $groupSchedule = [
                'ПН' => [],
                'ВТ' => [],
                'СР' => [],
                'ЧТ' => [],
                'ПТ' => [],
                'СБ' => [],
                'ВС' => [],
            ];

            // Заполняем расписание по дням недели
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
                            ];
                        }

                        if ($lesson->week_type === 'ЧИСЛ' && !empty($lesson)) {
                            $dayLessons[$index]['ЧИСЛ'] = new LessonResource($lesson);
                            if (empty($dayLessons[$index]['ЗНАМ'])) {
                                $dayLessons[$index]['ЗНАМ'] = new \stdClass();
                            }
                        }

                        if ($lesson->week_type === 'ЗНАМ' && !empty($lesson)) {
                            $dayLessons[$index]['ЗНАМ'] = new LessonResource($lesson);
                            if (empty($dayLessons[$index]['ЧИСЛ'])) {
                                $dayLessons[$index]['ЧИСЛ'] = new \stdClass();
                            }
                        }

                        if ($lesson->week_type === null) {
                            $dayLessons[$index]['lesson'] = new LessonResource($lesson);
                        }
                    }
                }

                ksort($dayLessons);
                // Преобразуем $dayLessons в плоский массив и добавляем его в расписание для дня недели
                $groupSchedule[$weekDay] = array_values($dayLessons);
            }

            // Преобразуем коллекцию зданий группы в массив имен зданий
            $buildings = $group->buildings->pluck('name')->toArray();

            // Если указан фильтр по корпусам, проверяем, попадает ли группа в нужные корпуса
            if ($buildingsFilterArray) {
                $intersectedBuildings = array_intersect($buildingsFilterArray, $buildings);

                // Если пересечения нет, пропускаем эту группу
                if (empty($intersectedBuildings)) {
                    continue;
                }
            }

            // Добавляем расписание группы в общий ответ
            $response[] = [
                'group' => new SkinnyGroup($group), // Информация о группе
                'buildings' => $buildings, // Связанные здания
                'schedule' => $groupSchedule // Расписание по дням недели
            ];
        }

        return response()->json($response);
    }
}
