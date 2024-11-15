<?php

namespace App\Http\Controllers;

use App\Facades\HistoryLogger;
use App\Http\Requests\Group\StoreGroupRequest;
// use App\Http\Requests\Group\AttachSemesterRequest;
// use App\Http\Requests\Group\DetachSemesterRequest;
use App\Http\Requests\Group\UpdateGroupRequest;
use App\Http\Resources\GroupResource;
use App\Http\Resources\LessonResource;
use App\Http\Resources\SkinnyGroup;
use App\Models\Group;
use App\Models\Schedule;
use App\Models\Semester;
use Illuminate\Http\Request;
use App\Http\Resources\SubjectResource;
use App\Http\Resources\TeacherResource;

class GroupController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Начинаем с запроса к модели Group
        $query = Group::query();

        if ($request->has(key: 'building')) {
            $query->where('building', $request->input('building'));
        }
        // Фильтрация по course
        if ($request->has('course')) {
            $query->where('course', $request->input('course'));
        }

        // Фильтрация по index
        if ($request->has('index')) {
            $query->where('index', $request->input('index'));
        }

        // Фильтрация по specialization
        if ($request->has('specialization')) {
            $query->where('specialization', $request->input('specialization'));
        }

        // Фильтрация по name (конкатенация specialization, course и index)
        if ($request->has('name')) {
            $name = $request->input('name');
            $query->where('name', 'ILIKE', "%{$name}%");
        }

        // Сортировка
        $orderField = $request->input('order_field', 'id'); // Поле для сортировки, по умолчанию id
        $orderDirection = $request->input('order_direction', 'desc'); // Направление сортировки, по умолчанию

        // Проверка на допустимые значения для сортировки
        if (in_array($orderField, ['id', 'course', 'index', 'specialization', 'name']) &&
            in_array($orderDirection, ['asc', 'desc'])) {
            $query->orderBy($orderField, $orderDirection);
        }

        // Получаем отфильтрованные группы
        $groups = $query->get();

        // Возвращаем коллекцию через GroupResource
        return GroupResource::collection($groups);
    }

    public function indexPublic(Request $request)
    {
        // Начинаем с запроса к модели Group
        $query = Group::query();

        $building = $request->input('building');
        if ($building) {
            $query->whereHas('buildings', function ($query) use ($building) {
                $query->where('name', $building);
            });
        }
        // Фильтрация по course
        if ($request->has('course')) {
            $query->where('course', $request->input('course'));
        }

        // Фильтрация по index
        if ($request->has('index')) {
            $query->where('index', $request->input('index'));
        }

        // Фильтрация по specialization
        if ($request->has('specialization')) {
            $query->where('specialization', $request->input('specialization'));
        }

        // Фильтрация по name (конкатенация specialization, course и index)
        if ($request->has('name')) {
            $name = $request->input('name');
            $query->where('name', 'LIKE', "%{$name}%");
        }

        // Сортировка
        $orderField = $request->input('order_field', 'id'); // Поле для сортировки, по умолчанию id
        $orderDirection = $request->input('order_direction', 'desc'); // Направление сортировки, по умолчанию

        // Проверка на допустимые значения для сортировки
        if (in_array($orderField, ['id', 'course', 'index', 'specialization', 'name']) &&
            in_array($orderDirection, ['asc', 'desc'])) {
            $query->orderBy($orderField, $orderDirection);
        }

        // Получаем отфильтрованные группы
        $groups = $query->get();

        // Возвращаем коллекцию через GroupResource
        return SkinnyGroup::collection($groups);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreGroupRequest $request)
    {
        $name = $request->name;

        $group = Group::create([
            ...$request->all(),
            'name' => $name,

        ]);
        $semesterIds = array_column($request->semesters, 'id');
        $group->semesters()->sync($semesterIds);
        $buildingsIds = array_column($request->buildings, 'name');
        $group->buildings()->sync($buildingsIds);
        $group->refresh();
        HistoryLogger::logAction('Добавлена группа '.$group->name, $group->toArray());

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
        HistoryLogger::logAction('Обновлена группа '.$group->name, $group->toArray());
        $group->update($request->all());
        if ($request->has('semesters') && is_array($request->semesters)) {
            $semestersIds = array_column($request->semesters, 'id');
            $group->semesters()->sync($semestersIds);
        }
        if ($request->has('buildings') && is_array($request->buildings)) {
            $buildingsIds = array_column($request->buildings, 'name');
            $group->buildings()->sync($buildingsIds);
        }
        $group->refresh();

        return new GroupResource($group);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Group $group)
    {
        HistoryLogger::logAction('Удалена группа '.$group->name, $group->toArray());
        $group->delete();

        return response()->noContent();
    }

    // /**
    //  * Attach a semester to a group
    //  */
    // public function attachSemester(AttachSemesterRequest $request, Group $group)
    // {
    //     $semester = Semester::findOrFail($request->semester_id);
    //     $group->semesters()->attach($semester);
    //     return response()->json(['message' => 'Семестр успешно добавлен к группе.', "semester" => $semester]);
    // }

    // /**
    //  * Detach a semester from a group
    //  */
    // public function detachSemester(DetachSemesterRequest $request, Group $group)
    // {
    //     $semester = Semester::findOrFail($request->semester_id);
    //     $group->semesters()->detach($semester);
    //     return response()->json(['message' => 'Семестр отвязан от группы.', "semester" => $semester ]);
    // }

    public function getCourses(Request $request)
    {
        $building = $request->query('building');
        $coursesQuery = Group::select('course')->distinct()->orderBy('course', 'asc');
        if ($building) {
            $coursesQuery->whereHas('buildings', function ($query) use ($building) {
                $query->where('name', $building);
            });
        }
        $courses = $coursesQuery->get();

        return $courses;
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

        // Инициализируем массив с днями недели
        $weekDays = ['ПН', 'ВТ', 'СР', 'ЧТ', 'ПТ', 'СБ', 'ВС'];

        // Формируем ответ
        $response = [];

        foreach ($weekDays as $day) {
            $response[] = [
                'week_day' => $day,
                'type' => 'main',
                'id' => null,
                'published' => null,
                'lessons' => [],
            ];
        }

        foreach ($schedules as $weekDay => $scheduleGroup) {
            $dayIndex = array_search($weekDay, $weekDays);

            $dayLessons = [];

            foreach ($scheduleGroup as $schedule) {
                $response[$dayIndex]['id'] = $schedule->id;
                $response[$dayIndex]['published'] = $schedule->published;

                foreach ($schedule->lessons as $lesson) {
                    $index = $lesson->index;

                    if (!isset($dayLessons[$index])) {
                        $dayLessons[$index] = [
                            'index' => $index,
                            'types' => [
                                [
                                    'week_type' => 'ЧИСЛ',
                                    'id' => null,
                                    'schedule_id' => null,
                                    'cabinet' => null,
                                    'building' => null,
                                    'subject' => null,
                                    'teachers' => null,
                                ],
                                [
                                    'week_type' => 'ЗНАМ',
                                    'id' => null,
                                    'schedule_id' => null,
                                    'cabinet' => null,
                                    'building' => null,
                                    'subject' => null,
                                    'teachers' => null,
                                ],
                            ],
                        ];
                    }

                    // Если есть lesson с week_type null, заменяем оба типа
                    if ($lesson->week_type === null) {
                        $dayLessons[$index]['types'] = [
                            [
                                'week_type' => null,
                                'id' => $lesson->id,
                                'schedule_id' => $lesson->schedule_id,
                                'cabinet' => $lesson->cabinet,
                                'building' => $lesson->building,
                                'subject' => new SubjectResource($lesson->subject),
                                'teachers' => TeacherResource::collection($lesson->teachers),
                            ],
                        ];
                    } elseif ($lesson->week_type === 'ЧИСЛ') {
                        $dayLessons[$index]['types'][0] = [
                            'week_type' => 'ЧИСЛ',
                            'id' => $lesson->id,
                            'schedule_id' => $lesson->schedule_id,
                            'cabinet' => $lesson->cabinet,
                            'building' => $lesson->building,
                            'subject' => new SubjectResource($lesson->subject),
                            'teachers' => TeacherResource::collection($lesson->teachers),
                        ];
                    } elseif ($lesson->week_type === 'ЗНАМ') {
                        $dayLessons[$index]['types'][1] = [
                            'week_type' => 'ЗНАМ',
                            'id' => $lesson->id,
                            'schedule_id' => $lesson->schedule_id,
                            'cabinet' => $lesson->cabinet,
                            'building' => $lesson->building,
                            'subject' => new SubjectResource($lesson->subject),
                            'teachers' => TeacherResource::collection($lesson->teachers),
                        ];
                    }
                }
            }

            ksort($dayLessons);
            $response[$dayIndex]['lessons'] = array_values($dayLessons);
        }

        return response()->json($response);
    }



    // public function scheduleMain(Group $group, Semester $semester)
    // {
    //     // Получаем расписания для данной группы с типом 'main'
    //     $schedules = Schedule::where('group_id', $group->id)
    //         ->where('semester_id', $semester->id)
    //         ->where('type', 'main')
    //         ->with('lessons') // Загрузить уроки вместе с расписанием
    //         ->get()
    //         ->groupBy('week_day');

    //     // Инициализируем пустой массив для ответа
    //     $response = [
    //         'ПН' => [],
    //         'ВТ' => [],
    //         'СР' => [],
    //         'ЧТ' => [],
    //         'ПТ' => [],
    //         'СБ' => [],
    //         'ВС' => [],
    //     ];
    //     // Заполняем ответ расписаниями по дням недели

    //     // Возвращаем ответ в формате JSON
    //     foreach ($schedules as $weekDay => $scheduleGroup) {
    //         // Подготавливаем массив для дня недели
    //         $dayLessons = [];

    //         // Сортируем расписание по времени начала урока
    //         foreach ($scheduleGroup as $schedule) {
    //             foreach ($schedule->lessons as $lesson) {
    //                 $index = $lesson->index; // Поле "index", указывающее номер пары

    //                 if (! isset($dayLessons[$index])) {
    //                     $dayLessons[$index] = [
    //                         // 'index' => $index,
    //                         // 'schedule_id' => $schedule->id,
    //                         'published' => $schedule->published,
    //                         // 'ЧИСЛ' => new \stdClass() ,
    //                         // 'ЗНАМ' => new \stdClass() ,
    //                     ];
    //                 }
    //                 if ($lesson->week_type === 'ЧИСЛ' && ! empty($lesson)) {
    //                     $dayLessons[$index]['ЧИСЛ'] = new LessonResource($lesson);
    //                     if (empty($dayLessons[$index]['ЗНАМ'])) {
    //                         $dayLessons[$index]['ЗНАМ'] = new \stdClass();
    //                     }
    //                 } if ($lesson->week_type === 'ЗНАМ' && ! empty($lesson)) {
    //                     $dayLessons[$index]['ЗНАМ'] = new LessonResource($lesson);
    //                     if (empty($dayLessons[$index]['ЧИСЛ'])) {
    //                         $dayLessons[$index]['ЧИСЛ'] = new \stdClass();
    //                     }
    //                     // $dayLessons[$index]['ЧИСЛ'] = new \stdClass();
    //                 }
    //                 if ($lesson->week_type === null) {
    //                     $dayLessons[$index] = new LessonResource($lesson);

    //                 }
    //             }
    //         }
    //         ksort($dayLessons);
    //         // Преобразуем $dayLessons в плоский массив и добавляем его в ответ
    //         $response[$weekDay] = array_values($dayLessons);
    //     }

    //     return response()->json($response);

    // }
}