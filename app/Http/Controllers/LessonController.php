<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLessonRequest;
use App\Http\Requests\Lesson\UpdateLessonRequest;
use App\Http\Resources\Lesson\LessonResource;
use App\Models\Lesson;
use App\Models\Teacher;
use Illuminate\Http\Request;

class LessonController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return LessonResource::collection(Lesson::all());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreLessonRequest $request)
    {
        $lesson = Lesson::create($request->all());
        return new LessonResource($lesson);
    }

    /**
     * Display the specified resource.
     */
    public function show(Lesson $lesson)
    {
        return new LessonResource($lesson);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateLessonRequest $request, Lesson $lesson)
    {
        $lesson->update($request->all());
        return new LessonResource($lesson);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Lesson $lesson)
    {
        $lesson->delete();
        return response()->noContent();
    }

    public function attachTeacher(Request $request, Lesson $lesson)
    {
        $teacher = Teacher::find($request->teacher_id);
        if (!$teacher) {
            return response()->json(['message' => 'Преподаватель не найден.'], 404);
        }

        if ($lesson->teachers()->where('teacher_id', $teacher->id)->exists()) {
            return response()->json(['message' => 'Этот преподаватель уже добавлен к этой паре.'], 409);
        }
        $lesson->teachers()->attach($teacher);
        return response()->json(['message' => 'Преподаватель успешно добавлен к паре.', "teacher" => $teacher]);
    }

    public function detachTeacher(Request $request, Lesson $lesson)
    {
        $teacher = Teacher::find($request->teacher_id);

        if (!$teacher) {
            return response()->json(['message' => 'Преподаватель не найден.'], 404);
        }

        if (!$lesson->teachers()->where('teacher_id', $teacher->id)->exists()) {
            return response()->json(['message' => 'Этот преподаватель не прикреплен к этой паре.'], 409);
        }
        $lesson->teachers()->detach($teacher);
        return response()->json(['message' => 'Преподаватель отвязан от пары.', "teacher" => $teacher ]);
    }
}
