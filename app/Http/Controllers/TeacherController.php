<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
// use App\Http\Requests\Teacher\AttachSubjectRequest;
// use App\Http\Requests\Teacher\DetachSubjectRequest;
use App\Http\Requests\Teacher\StoreTeacherRequest;
use App\Http\Requests\Teacher\UpdateTeacherRequest;
use App\Http\Resources\TeacherResource;
use App\Models\Teacher;
use Illuminate\Http\Request;
use App\Facades\HistoryLogger;

class TeacherController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Teacher::query();

        if ($request->has('name')) {
            $name = $request->input('name');
            $query->where('name', 'ILIKE', "%{$name}%");
        }
        if ($request->has('subject_id')) {
            $subjectId = $request->input('subject_id');
            $query->whereHas('subjects', function ($q) use ($subjectId) {
                $q->where('subject_id', $subjectId);
            });
        }

        $orderField = $request->input('order_field', 'id'); // Поле для сортировки, по умолчанию id
        $orderDirection = $request->input('order_direction', 'desc'); // Направление сортировки, по умолчанию

        if (in_array($orderField, ['id', 'name', 'created_at', 'updated_at']) &&
        in_array($orderDirection, ['asc', 'desc'])) {
            $query->orderBy($orderField, $orderDirection);
        }

        // Получаем отфильтрованные группы
        $teachers = $query->get();
        return TeacherResource::collection($teachers);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTeacherRequest $request)
    {
        $teacher = Teacher::create($request->all());
        HistoryLogger::logAction('Добавлен преподаватель', $teacher->toArray());
        return new TeacherResource($teacher);
    }

    /**
     * Display the specified resource.
     */
    public function show(Teacher $teacher)
    {
        return new TeacherResource($teacher);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTeacherRequest $request, Teacher $teacher)
    {
        HistoryLogger::logAction('Обновлен преподаватель', $teacher->toArray());
        $teacher->update($request->all());
        if ($request->has('subjects') && is_array($request->subjects)) {
            $subjectsIds = array_column($request->subjects, 'id');
            $teacher->subjects()->sync($subjectsIds);
        }
        return new TeacherResource($teacher);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Teacher $teacher)
    {
        HistoryLogger::logAction('Удален преподаватель', $teacher->toArray());
        $teacher->delete();
        return response()->noContent();
    }

    // /**
    //  * Attach a subject to a teacher
    //  */
    // public function attachSubject(AttachSubjectRequest $request, Teacher $teacher)
    // {
    //     $subject = Subject::findOrFail($request->subject_id);
    //     $teacher->subjects()->attach($subject);
    //     return response()->json(['message' => 'Предмет успешно добавлен к преподавателю.', "subject" => $subject]);
    // }

    // /**
    //  * Detach a subject from a teacher
    //  */
    // public function detachSubject(DetachSubjectRequest $request, Teacher $teacher)
    // {
    //     $subject = Subject::findOrFail($request->subject_id);
    //     $teacher->subjects()->detach($subject);
    //     return response()->json(['message' => 'Предмет отвязан от преподавателя.', "subject" => $subject ]);
    // }
}
