<?php

namespace App\Http\Controllers;

use App\Facades\HistoryLogger;
use App\Http\Requests\Schedule\StoreScheduleRequest;
use App\Http\Requests\Schedule\UpdateScheduleRequest;
use App\Http\Resources\LessonResource;
use App\Http\Resources\ScheduleResource;
use App\Http\Resources\SemesterResource;
use App\Http\Resources\SkinnyGroup;
use App\Models\Group;
use App\Models\Lesson;
use App\Models\Schedule;
use App\Models\Teacher;
use App\Models\Subject;
use App\Models\Semester;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\TeacherResource;

class ScheduleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    // public function index()
    // {
    //     return ScheduleResource::collection(Schedule::all());
    // }

    /**
     * Summary of store
     *
     * @return ScheduleResource
     */
    public function store(StoreScheduleRequest $request)
    {
        $data = $request->all();
        $schedule = Schedule::create($data);
        HistoryLogger::logAction(
            'Добавлено '.($schedule->type === 'main' ? 'основное' : 'измененное').
            ' расписание на '.($schedule->week_day ?? $schedule->date).' для группы '.$schedule->group?->name
        );

        return new ScheduleResource($schedule);
    }

    /**
     * Summary of show
     *
     * @return ScheduleResource
     */
    public function show(Schedule $schedule)
    {
        return new ScheduleResource($schedule);
    }

    /**
     * Summary of update
     *
     * @return ScheduleResource
     */
    public function update(UpdateScheduleRequest $request, Schedule $schedule)
    {
        $schedule->update($request->all());
        HistoryLogger::logAction(
            action: 'Обновлено '.($schedule->type === 'main' ? 'основное' : 'измененное').
            ' расписание на '.($schedule->week_day ?? Carbon::parse($schedule->date)->translatedFormat('d F Y')).' для группы '.$schedule->group?->name
        );
        return new ScheduleResource($schedule);
    }

    /**
     * Summary of destroy
     *
     * @return mixed|\Illuminate\Http\Response
     */
    public function destroy(Schedule $schedule)
    {
        HistoryLogger::logAction(
            'Удалено '.($schedule->type === 'main' ? 'основное' : 'измененное').
            ' расписание на '.($schedule->week_day ?? $schedule->date).' для группы '.$schedule->group?->name
        );
        $schedule->delete();

        return response()->noContent();
    }

    /**
     * Summary of fromMainToChangesSchedule
     *
     * @return ScheduleResource
     */
    public function fromMainToChangesSchedule(Request $request, Schedule $schedule)
    {
        $carbonDate = Carbon::parse($request->input('date'));
        $newSchedule = $schedule->replicate();
        $newSchedule->type = 'changes';
        $newSchedule->date = $carbonDate;
        $newSchedule->week_day = null;
        $newSchedule->published = false;
        $newSchedule->save();

        foreach ($schedule->lessons as $lesson) {
            $newLesson = $lesson->replicate();
            $newLesson->schedule_id = $newSchedule->id;

            $weekNumber = Carbon::parse($newSchedule->semester->start)->diffInWeeks($request->input('date'));

            $weekType = ($weekNumber % 2 === 0) ? 'ЧИСЛ' : 'ЗНАМ';
            if ($lesson->week_type === $weekType || $lesson->week_type === null) {
                $newLesson->save();
                $newLesson->teachers()->attach($lesson->teachers->pluck('id'));
            }

        }

        return new ScheduleResource($newSchedule);
    }

    /**
     * Summary of createScheduleWithChanges
     *
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function createScheduleWithChanges(Request $request)
    {
        $scheduleData = $request->json()->all();

        $group = DB::table('groups')->find($scheduleData['group_id']);
        if (! $group) {
            return response()->json(['error' => 'Группа не найдена'], 404);
        }

        if (! isset($scheduleData['semester_id']) || ! DB::table('semesters')->find($scheduleData['semester_id'])) {
            return response()->json(['error' => 'Семестр не найден'], 404);
        }
        $carbonDate = Carbon::parse($request->input('date'));
        $newScheduleId = DB::table('schedules')->insertGetId([
            'group_id' => $scheduleData['group_id'],
            'type' => 'changes',
            'date' => $carbonDate,
            'semester_id' => $scheduleData['semester_id'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        if (isset($scheduleData['lessons']) && is_array($scheduleData['lessons']) && ! empty($scheduleData['lessons'])) {
            foreach ($scheduleData['lessons'] as $lessonData) {
                $lessonId = DB::table('lessons')->insertGetId([
                    'schedule_id' => $newScheduleId,
                    'subject_id' => $lessonData['subject']['id'],
                    'index' => $lessonData['index'],
                    'week_type' => $lessonData['week_type'],
                    'cabinet' => $lessonData['cabinet'],
                    'building' => $lessonData['building'],
                    'message' => $lessonData['message'] ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                if (! empty($lessonData['teachers'])) {
                    foreach ($lessonData['teachers'] as $teacher) {
                        DB::table('lesson_teacher')->insert([
                            'lesson_id' => $lessonId,
                            'teacher_id' => $teacher['id'],
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }
        }

        return response()->json([
            'schedule_id' => $newScheduleId,
            'message' => 'Новое расписание с изменениями успешно создано',
        ]);
    }

    /**
     * Summary of getChangesSchedules
     *
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function getChangesSchedules(Request $request)
    {
        $request->validate([
            'date' => ['required', 'date'],
        ]);
        $date = $request->input('date');
        $carbonDate = Carbon::parse($date);

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

        $semester = DB::table('semesters')
            ->where('start', '<=', $carbonDate)
            ->where('end', '>=', $carbonDate)
            ->first();

        if (! $semester) {
            return response()->json([
                'error' => 404,
                'message' => 'Семестра на данную дату не найдено',
            ], 404);
        }

        $semesterStart = Carbon::parse($semester->start);
        $weekNumber = $semesterStart->diffInWeeks($carbonDate);
        $weekType = ($weekNumber % 2 === 0) ? 'ЧИСЛ' : 'ЗНАМ';

        $groupQuery = DB::table('groups')->select('id', 'name');

        if ($building = $request->input('building')) {
            $groupQuery->whereExists(function ($query) use ($building) {
                $query->select(DB::raw(1))
                    ->from('group_building')
                    ->join('buildings', 'group_building.building_name', '=', 'buildings.name')
                    ->where('buildings.name', $building)
                    ->whereColumn('group_building.group_id', 'groups.id');
            });
        }

        if ($course = $request->input('course')) {
            $groupQuery->where('course', $course);
        }

        if ($groupName = $request->input('group')) {
            $groupQuery->where('name', 'ILIKE', "%{$groupName}%");
        }

        $groups = $groupQuery->get();

        if ($groups->isEmpty()) {
            return response()->json([
                'week_type' => $weekType,
                'last_updated' => null,
                'schedules' => [],
            ]);
        }

        $query = "
            SELECT  g.id as group_id,
                    g.name as group_name,
                    s.id as schedule_id, 
                    s.published as published, 
                    s.week_day, 
                    s.type, 
                    s.updated_at,
                    json_build_object(
                        'id', g.id,
                        'name', g.name
                    ) as group,
                    json_agg(
                        json_build_object(
                            'id', l.id, 
                            'index', l.index, 
                            'schedule_id', s.id, 
                            'subject', json_build_object(
                                'id', subj.id,
                                'name', subj.name
                            ), 
                            'week_type', l.week_type, 
                            'cabinet', l.cabinet,
                            'building', l.building,
                            'message', l.message,
                            'teachers', COALESCE(
                                (
                                    SELECT json_agg(
                                        json_build_object(
                                            'id', t.id,
                                            'name', t.name
                                        )
                                    )
                                    FROM lesson_teacher lt
                                    JOIN teachers t ON lt.teacher_id = t.id
                                    WHERE lt.lesson_id = l.id
                                ), '[]'::json
                            ) 
                        )
                    ) as lessons
            FROM groups g
            LEFT JOIN schedules s ON g.id = s.group_id
            LEFT JOIN lessons l ON s.id = l.schedule_id
            LEFT JOIN subjects subj ON l.subject_id = subj.id
         
            WHERE (
                (s.type = 'changes' AND s.date = :date)
                OR (s.type = 'main' AND s.published = TRUE AND s.week_day = :week_day AND s.semester_id = :semester_id)
            )
        ";

        $params = [
            'date' => $carbonDate->toDateString(),
            'week_day' => $weekDay,
            'semester_id' => $semester->id,
        ];

        if ($building) {
            $query .= '
                AND EXISTS (
                    SELECT 1
                    FROM group_building gb
                    JOIN buildings b ON gb.building_name = b.name
                    WHERE gb.group_id = g.id
                    AND b.name = :building
                )
            ';
            $params['building'] = $building;
        }

        if ($course) {
            $query .= ' AND g.course = :course ';
            $params['course'] = $course;
        }

        if ($groupName) {
            $query .= ' AND g.name ILIKE :group_name ';
            $params['group_name'] = "%{$groupName}%";
        }

        $query .= ' GROUP BY g.name, s.id, g.id ORDER BY g.name';

        $schedules = DB::select($query, $params);

        $finalSchedules = [
            'week_type' => $weekType,
            'last_updated' => null,
            'semester' => $semester,
            'schedules' => [],
        ];

        $groupSchedules = [];

        foreach ($groups as $group) {
            $groupSchedules[$group->name] = [
                'group' => [
                    'id' => $group->id,
                    'name' => $group->name,
                ],
            ];
        }

        foreach ($schedules as $schedule) {
            $schedule->lessons = json_decode($schedule->lessons, true);
            $schedule->group = json_decode($schedule->group);

            $filteredLessons = [];
            foreach ($schedule->lessons as $lesson) {
                if ($lesson['week_type'] === $weekType || $lesson['week_type'] === null && $lesson['id'] !== null) {
                    $filteredLessons[] = $lesson;
                }
            }

            if (empty($filteredLessons)) {
                continue;
            }

            usort($filteredLessons, function ($a, $b) {
                return $a['index'] <=> $b['index'];
            });

            if (is_null($finalSchedules['last_updated']) || $schedule->updated_at > $finalSchedules['last_updated']) {
                $finalSchedules['last_updated'] = $schedule->updated_at;
            }

            if ($schedule->type === 'changes') {
                $groupSchedules[$schedule->group->name] = [
                    'group' => [
                    'id' => $schedule->group->id,
                    'name' => $schedule->group->name,
                ],
                    'schedule_id' => $schedule->schedule_id,
                    'week_day' => $schedule->week_day,
                    'published' => $schedule->published,
                    'type' => 'changes',
                    'lessons' => $filteredLessons,
                ];
            } elseif (! isset($groupSchedules[$schedule->group->name]['lessons'])) {
                $groupSchedules[$schedule->group->name] = [
                    'group' => [
                    'id' => $schedule->group->id,
                    'name' => $schedule->group->name,
                ],
                    'schedule_id' => $schedule->schedule_id,
                    'week_day' => $schedule->week_day,
                    'published' => $schedule->published,
                    'type' => $schedule->type,
                    'lessons' => $filteredLessons,
                ];
            }
        }

        $finalSchedules['schedules'] = array_values($groupSchedules);

        return response()->json($finalSchedules);
    }

    /**
     * Summary of getPublicSchedules
     *
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function getPublicSchedules(Request $request)
    {
        $request->validate([
            'date' => ['required', 'date'],
        ]);
        $date = $request->input('date');
        $carbonDate = Carbon::parse($date);

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

        $semester = DB::table('semesters')
            ->where('start', '<=', $carbonDate)
            ->where('end', '>=', $carbonDate)
            ->first();

        if (! $semester) {
            return response()->json([
                'error' => 404,
                'message' => 'Семестра на данную дату не найдено',
            ], 404);
        }

        $semesterStart = Carbon::parse($semester->start);
        $weekNumber = $semesterStart->diffInWeeks($carbonDate);
        $weekType = ($weekNumber % 2 === 0) ? 'ЧИСЛ' : 'ЗНАМ';

        $groupsWithChanges = DB::table('schedules')
            ->select('group_id')
            ->where('published', true)
            ->where('type', 'changes')
            ->where('date', $carbonDate->toDateString())
            ->pluck('group_id')
            ->toArray();

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

        $params = [
            'date' => $carbonDate->toDateString(),
            'week_day' => $weekDay,
            'semester_id' => $semester->id,
        ];

        if ($building = $request->input('building')) {
            $query .= '
                AND EXISTS (
                    SELECT 1 
                    FROM group_building gb
                    JOIN buildings b ON gb.building_name = b.name
                    WHERE gb.group_id = g.id 
                    AND b.name = :building
                )
            ';
            $params['building'] = $building;
        }

        if ($course = $request->input('course')) {
            $query .= ' AND g.course = :course ';
            $params['course'] = $course;
        }

        if ($groupName = $request->input('group')) {
            $query .= ' AND g.name LIKE :group_name ';
            $params['group_name'] = "%{$groupName}%";
        }

        if ($cabinet = $request->input('cabinet')) {
            $query .= ' AND l.cabinet ILIKE :cabinet ';
            $params['cabinet'] = "%{$cabinet}%";
        }

        if ($subject = $request->input('subject')) {
            $query .= ' AND subj.name ILIKE :subject ';
            $params['subject'] = "%{$subject}%";
        }

        if ($teacher = $request->input('teacher')) {
            $query .= '
                AND EXISTS (
                    SELECT 1 FROM lesson_teacher lt
                    JOIN teachers t ON lt.teacher_id = t.id
                    WHERE lt.lesson_id = l.id
                    AND t.name ILIKE :teacher_name
                )
            ';
            $params['teacher_name'] = "%{$teacher}%";
        }

        if (! empty($groupsWithChanges)) {
            $query .= " AND (s.type = 'changes' OR g.id NOT IN (".implode(',', $groupsWithChanges).'))';
        }

        $query .= ' GROUP BY g.name, s.id ORDER BY g.name';

        $schedules = DB::select($query, $params);

        $finalSchedules = [
            'week_type' => $weekType,
            'last_updated' => null,
            'schedules' => [],
        ];

        $groupSchedules = [];

        foreach ($schedules as $schedule) {
            $schedule->lessons = json_decode($schedule->lessons, true);

            $filteredLessons = [];
            foreach ($schedule->lessons as $lesson) {
                if ($lesson['week_type'] === $weekType || $lesson['week_type'] === null) {
                    $filteredLessons[] = $lesson;
                }
            }

            if (empty($filteredLessons)) {
                continue;
            }

            usort($filteredLessons, function ($a, $b) {
                return $a['index'] <=> $b['index'];
            });

            if (is_null($finalSchedules['last_updated']) || $schedule->updated_at > $finalSchedules['last_updated']) {
                $finalSchedules['last_updated'] = $schedule->updated_at;
            }

            $groupSchedules[$schedule->group_name] = [
                'group_name' => $schedule->group_name,
                'week_day' => $schedule->week_day,
                'type' => $schedule->type,
                'lessons' => $filteredLessons,

            ];
        }

        $finalSchedules['schedules'] = array_values($groupSchedules);

        usort($finalSchedules['schedules'], function ($a, $b) {
            return ! empty($b) <=> ! empty($a);
        });

        return response()->json($finalSchedules);
    }

    /**
     * Summary of getScheduleByDatePrint
     *
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function getChangesSchedulesPrint(Request $request)
    {
        $request->validate([
            'date' => ['required', 'date'],
        ]);
        $date = $request->input('date');

        $carbonDate = Carbon::parse($date);

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

        $groupsQuery = Group::query();
        $course = $request->input('course');
        if ($course) {
            $groupsQuery->where('course', $course);
        }
        $groups = $groupsQuery->get();

        $changes = Schedule::where('type', 'changes')
            ->where('date', $carbonDate)
            ->get();

        $semester = Semester::where('start', '<=', $carbonDate)
            ->where('end', '>=', $carbonDate)
            ->first();

        if (! $semester) {
            return response()->json([
                'error' => 404,
                'message' => 'Семестра на данную дату не найдено',
            ], 404);
        }

        $semesterStart = Carbon::parse($semester->start);
        $weekNumber = $semesterStart->diffInWeeks($carbonDate);

        $weekType = ($weekNumber % 2 === 0) ? 'ЧИСЛ' : 'ЗНАМ';

        $finalSchedules = [
            '1-5' => [
                'week_type' => $weekType,
                'schedules' => [],
            ],
            '6' => [
                'week_type' => $weekType,
                'schedules' => [],
            ],
        ];

        foreach ($groups as $group) {
            $groupChanges = $changes->where('group_id', $group->id);

            $groupSchedule = [];

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

            if (empty($groupSchedule)) {
                continue;
            }

            $buildings = $group->buildings;

            foreach ($buildings as $building) {
                $buildingKey = $building->name == '6' ? '6' : '1-5';

                $filteredSemester = $group->semesters->filter(function ($semester) use ($carbonDate) {
                    $start = Carbon::parse($semester->start);
                    $end = Carbon::parse($semester->end);

                    return $carbonDate->between($start, $end);
                })->first();

                array_push($finalSchedules[$buildingKey]['schedules'], [
                    'semester' => $filteredSemester ? new SemesterResource($filteredSemester) : null,
                    'group' => new SkinnyGroup($group),
                    'schedule' => $groupSchedule,
                ]);
            }
        }

        $finalSchedules = array_filter($finalSchedules, function ($buildingSchedule) {
            return ! empty($buildingSchedule['schedules']);
        });

        return response()->json($finalSchedules);
    }

    /**
     * Summary of getSchedulesMainPrint
     *
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function getSchedulesMainPrint(Semester $semester, Request $request)
    {
        $course = $request->query('course');
        $buildingsFilter = $request->query('buildings', null);
        $groupsQuery = Group::query();
        if ($course) {
            $groupsQuery->where('course', intval($course));
        }

        $groups = $groupsQuery->with('buildings')->get();

        $buildingsFilterArray = null;
        if ($buildingsFilter) {
            $buildingsFilterArray = array_map('trim', explode(',', $buildingsFilter));
        }

        $response = [];

        foreach ($groups as $group) {
            $schedules = Schedule::where('group_id', $group->id)
                ->where('semester_id', $semester->id)
                ->where('type', 'main')
                ->where('published', true)
                ->with('lessons')
                ->get()
                ->groupBy('week_day');

            $groupSchedule = [
                'ПН' => [],
                'ВТ' => [],
                'СР' => [],
                'ЧТ' => [],
                'ПТ' => [],
                'СБ' => [],
                'ВС' => [],
            ];

            foreach ($schedules as $weekDay => $scheduleGroup) {
                $dayLessons = [];

                foreach ($scheduleGroup as $schedule) {
                    foreach ($schedule->lessons as $lesson) {
                        $index = $lesson->index;

                        if (! isset($dayLessons[$index])) {
                            $dayLessons[$index] = [
                                'index' => $index,
                            ];
                        }

                        if ($lesson->week_type === 'ЧИСЛ' && ! empty($lesson)) {
                            $dayLessons[$index]['ЧИСЛ'] = new LessonResource($lesson);
                            if (empty($dayLessons[$index]['ЗНАМ'])) {
                                $dayLessons[$index]['ЗНАМ'] = new \stdClass();
                            }
                        }

                        if ($lesson->week_type === 'ЗНАМ' && ! empty($lesson)) {
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

                $groupSchedule[$weekDay] = array_values($dayLessons);
            }

            $buildings = $group->buildings->pluck('name')->toArray();

            if ($buildingsFilterArray) {
                $intersectedBuildings = array_intersect($buildingsFilterArray, $buildings);

                if (empty($intersectedBuildings)) {
                    continue;
                }
            }

            $response[] = [
                'group' => new SkinnyGroup($group),
                'buildings' => $buildings,
                'schedule' => $groupSchedule,
            ];
        }

        return response()->json($response);
    }

    /**
     * Summary of getLessonCountsByDateRange
     *
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function getLessonCountsByDateRange(Request $request)
    {
        $request->validate([
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date'],
            'group_ids' => ['nullable'],
            'group_ids.*' => ['exists:groups,id'],
        ]);
        $startDate = Carbon::parse($request->input('start_date'));
        $endDate = Carbon::parse($request->input('end_date'));
        $groupIds = $request->input('group_ids');

        if (is_string($groupIds)) {
            $groupIds = explode(',', $groupIds);
        }

        if (empty($groupIds)) {
            $groupIds = DB::table('groups')->pluck('id')->toArray();
        }

        $weekDayMapping = [
            0 => 'ВС',
            1 => 'ПН',
            2 => 'ВТ',
            3 => 'СР',
            4 => 'ЧТ',
            5 => 'ПТ',
            6 => 'СБ',
        ];

        $finalGroups = [];

        $currentDate = $startDate->copy();
        while ($currentDate->lte($endDate)) {
            $weekDay = $weekDayMapping[$currentDate->dayOfWeek];

            $semester = DB::table('semesters')
                ->where('start', '<=', $currentDate)
                ->where('end', '>=', $currentDate)
                ->first();

            if (! $semester) {
                $currentDate->addDay();

                continue;
            }

            $semesterStart = Carbon::parse($semester->start);
            $weekNumber = $semesterStart->diffInWeeks($currentDate);
            $weekType = ($weekNumber % 2 === 0) ? 'ЧИСЛ' : 'ЗНАМ';

            $query = '
                SELECT g.name as group_name, 
                       subj.name as subject_name,
                       COUNT(l.id) as lesson_count,
                       l.week_type as lesson_week_type
                FROM groups g
                LEFT JOIN schedules s ON g.id = s.group_id
                LEFT JOIN lessons l ON s.id = l.schedule_id
                LEFT JOIN subjects subj ON l.subject_id = subj.id
                WHERE s.published = true
                AND subj.name IS NOT NULL
                AND l.message IS NULL
                AND g.id IN ('.implode(',', array_map('intval', $groupIds)).")
                AND (
                    (s.type = 'changes' AND s.date = :date)
                    OR (
                        s.type = 'main' 
                        AND s.week_day = :week_day 
                        AND s.semester_id = :semester_id
                        AND NOT EXISTS (
                            SELECT 1
                            FROM schedules s2
                            WHERE s2.group_id = s.group_id
                            AND s2.type = 'changes'
                            AND s2.date = :date
                        )
                    )
                )
                GROUP BY g.name, subj.name, l.week_type
                ORDER BY g.name;
            ";

            $params = [
                'date' => $currentDate->toDateString(),
                'week_day' => $weekDay,
                'semester_id' => $semester->id,
            ];

            $results = DB::select($query, $params);

            foreach ($results as $result) {
                if (! isset($finalGroups[$result->group_name])) {
                    $finalGroups[$result->group_name] = [
                        'group_name' => $result->group_name,
                        'subjects' => [],
                    ];
                }

                if (! isset($finalGroups[$result->group_name]['subjects'][$result->subject_name])) {
                    $finalGroups[$result->group_name]['subjects'][$result->subject_name] = 0;
                }

                if ($result->lesson_week_type === $weekType || $result->lesson_week_type === null) {
                    $finalGroups[$result->group_name]['subjects'][$result->subject_name] += $result->lesson_count * 2;
                }
            }

            $currentDate->addDay();
        }

        $finalGroupsArray = array_values($finalGroups);

        return response()->json($finalGroupsArray);
    }

    /**
     * Возвращает массив дат с расписанием по дням из диапазона дат
     *
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function getSchedulesByDateRange(Request $request)
    {
        $request->validate([
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date'],
            'group_ids' => ['required'],
            'group_ids.*' => ['exists:groups,id'],
        ]);
        $startDate = Carbon::parse($request->input('start_date'));
        $endDate = Carbon::parse($request->input('end_date'));
        $groupIds = $request->input('group_ids');

        if (is_string($groupIds)) {
            $groupIds = explode(',', $groupIds);
        }

        $weekDayMapping = [
            0 => 'ВС',
            1 => 'ПН',
            2 => 'ВТ',
            3 => 'СР',
            4 => 'ЧТ',
            5 => 'ПТ',
            6 => 'СБ',
        ];

        $finalSchedules = [];

        $currentDate = $startDate->copy();
        while ($currentDate->lte($endDate)) {
            $weekDay = $weekDayMapping[$currentDate->dayOfWeek];

            $semester = DB::table('semesters')
                ->where('start', '<=', $currentDate)
                ->where('end', '>=', $currentDate)
                ->first();

            if (! $semester) {
                $currentDate->addDay();

                continue;
            }

            $semesterStart = Carbon::parse($semester->start);
            $weekNumber = $semesterStart->diffInWeeks($currentDate);
            $weekType = ($weekNumber % 2 === 0) ? 'ЧИСЛ' : 'ЗНАМ';

            $query = "
                SELECT g.name as group_name, 
                       s.id as schedule_id, 
                       s.week_day, 
                       s.type, 
                       s.updated_at,
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
                AND g.id IN (".implode(',', array_map('intval', $groupIds)).')
                GROUP BY g.name, s.id, s.week_day, s.type, s.updated_at
                ORDER BY s.type DESC;
            ';

            $params = [
                'date' => $currentDate->toDateString(),
                'week_day' => $weekDay,
                'semester_id' => $semester->id,
            ];

            $schedules = DB::select($query, $params);

            $groupSchedules = [];

            foreach ($schedules as $schedule) {
                $schedule->lessons = json_decode($schedule->lessons, true);

                $filteredLessons = [];
                foreach ($schedule->lessons as $lesson) {
                    if ($lesson['week_type'] === $weekType || $lesson['week_type'] === null) {
                        $filteredLessons[] = $lesson;
                    }
                }

                if (empty($filteredLessons)) {
                    continue;
                }

                usort($filteredLessons, function ($a, $b) {
                    return $a['index'] <=> $b['index'];
                });

                if (! isset($groupSchedules[$schedule->group_name]) || $schedule->type === 'changes') {
                    $groupSchedules[$schedule->group_name] = [
                        'group_name' => $schedule->group_name,
                        'schedule' => [
                            'week_day' => $schedule->week_day,
                            'type' => $schedule->type,
                            'lessons' => $filteredLessons,
                        ],
                    ];
                }
            }

            if (! empty($groupSchedules)) {
                $finalSchedules[$currentDate->toDateString()] = array_values($groupSchedules);
            }

            $currentDate->addDay();
        }

        return response()->json($finalSchedules);
    }

    public function getMainScheduleSubjects(Request $request)
    {
        $data = $request->validate([
            'date' => 'required|date',
            'group_id' => 'required|integer|exists:groups,id',
        ]);

        // Получаем семестр, к которому относится дата
        $semester = Semester::whereDate('start', '<=', $data['date'])
            ->whereDate('end', '>=', $data['date'])
            ->first();

        if (!$semester) {
            return response()->json(['message' => 'Семестр не найден для указанной даты'], 404);
        }

        // Получаем все расписания с типом 'main' для нужного семестра и группы
        $schedules = Schedule::where('type', 'main')
            ->where('semester_id', $semester->id)
            ->where('group_id', $data['group_id'])
            ->pluck('id');

        if ($schedules->isEmpty()) {
            return response()->json(['message' => 'Основное расписание не найдено для указанной группы'], 404);
        }

        // Получаем уникальные предметы, связанные с уроками всех расписаний
        $subjects = Subject::whereHas('lessons', function ($query) use ($schedules) {
            $query->whereIn('schedule_id', $schedules);
        })->distinct()->get();

        return response()->json($subjects);
    }

    public function getMainScheduleTeachers(Request $request)
    {
        $data = $request->validate([
            'date' => 'required|date',
            'group_id' => 'required|integer|exists:groups,id',
            'subject_id' => 'required|integer|exists:subjects,id',
        ]);

        // Получаем семестр, к которому относится дата
        $semester = Semester::whereDate('start', '<=', Carbon::parse($data['date']))
            ->whereDate('end', '>=', Carbon::parse($data['date']))
            ->first();

        if (!$semester) {
            return response()->json(['message' => 'Семестр не найден для указанной даты'], 404);
        }

        // Получаем все расписания с типом 'main' для нужного семестра и группы
        $schedules = Schedule::where('type', 'main')
            ->where('semester_id', $semester->id)
            ->where('group_id', $data['group_id'])
            ->pluck('id');

        if ($schedules->isEmpty()) {
            return response()->json(['message' => 'Основное расписание не найдено для указанной группы'], 404);
        }

        // Получаем уникальных преподавателей, которые ведут указанный предмет в этих расписаниях
        $teachers = Teacher::whereHas('lessons', function ($query) use ($schedules, $data) {
            $query->whereIn('schedule_id', $schedules)
                  ->where('subject_id', $data['subject_id']);
        })->distinct()->get();

        return TeacherResource::collection($teachers);

    }


}
