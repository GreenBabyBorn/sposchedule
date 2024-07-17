<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\Teacher\AttachSubjectRequest;
use App\Http\Requests\Teacher\DetachSubjectRequest;
use App\Http\Requests\Teacher\StoreTeacherRequest;
use App\Http\Requests\Teacher\UpdateTeacherRequest;
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
    public function attachSubject(AttachSubjectRequest $request, Teacher $teacher)
    {
        $subject = Subject::findOrFail($request->subject_id);
        $teacher->subjects()->attach($subject);
        return response()->json(['message' => 'Предмет успешно добавлен к преподавателю.', "subject" => $subject]);
    }

    /**
     * Detach a subject from a teacher
     */
    public function detachSubject(DetachSubjectRequest $request, Teacher $teacher)
    {
        $subject = Subject::findOrFail($request->subject_id);
        $teacher->subjects()->detach($subject);
        return response()->json(['message' => 'Предмет отвязан от преподавателя.', "subject" => $subject ]);
    }
}
