<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTeacherRequest;
use App\Http\Requests\UpdateTeacherRequest;
use App\Http\Resources\TeacherResource;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Http\Request;

class TeacherController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return TeacherResource::collection(Teacher::all());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTeacherRequest $request)
    {
        $teacher = Teacher::create($request->all());
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
        $teacher->update($request->all());
        return new TeacherResource($teacher);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Teacher $teacher)
    {
        $teacher->delete();
        return response()->noContent();
    }

    /**
     * Attach a subject to a teacher
     */
    public function attachSubject(Request $request, Teacher $teacher)
    {
        $subject = Subject::find($request->subject_id);
        if (!$subject) {
            return response()->json(['message' => 'Предмет не найден.'], 404);
        }

        if ($teacher->subjects()->where('subject_id', $subject->id)->exists()) {
            return response()->json(['message' => 'Этот предмент уже добавлен к этому преподавателю.'], 409);
        }
        $teacher->subjects()->attach($subject);
        return response()->json(['message' => 'Предмет успешно добавлен к преподавателю.', "subject"=>$subject]);
    }

    /**
     * Detach a subject from a teacher
     */
    public function detachSubject(Request $request, Teacher $teacher)
    {
        $subject = Subject::find($request->subject_id);

        if (!$subject) {
            return response()->json(['message' => 'Предмет не найден.'], 404);
        }

        if (!$teacher->subjects()->where('subject_id', $subject->id)->exists()) {
            return response()->json(['message' => 'Этот предмет не прикреплен к этому преподавтелю.'], 409);
        }
        $teacher->subjects()->detach($subject);
        return response()->json(['message' => 'Предмет отвязан от преподавателя.', "subject"=>$subject ]);
    }
}
