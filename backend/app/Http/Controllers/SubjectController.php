<?php

namespace App\Http\Controllers;

use App\Facades\HistoryLogger;
use App\Http\Requests\Subject\StoreSubjectRequest;
use App\Http\Requests\Subject\UpdateSubjectRequest;
use App\Http\Resources\SubjectResource;
use Illuminate\Http\Request;
use App\Models\Subject;
use App\Models\Lesson;
use App\Models\Teacher;
use Illuminate\Support\Facades\DB;

class SubjectController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Subject::query();
        $orderField = $request->input('order_field', 'id'); // Поле для сортировки, по умолчанию id
        $orderDirection = $request->input('order_direction', 'desc'); // Направление сортировки, по умолчанию

        if (in_array($orderField, ['id', 'name', 'created_at', 'updated_at']) &&
        in_array($orderDirection, ['asc', 'desc'])) {
            $query->orderBy($orderField, $orderDirection);
        }

        // Получаем отфильтрованные группы
        $subjects = $query->get();

        return SubjectResource::collection($subjects);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreSubjectRequest $request)
    {
        $subject = Subject::create($request->all());
        HistoryLogger::logAction('Добавлен предмет', $subject->toArray());

        return new SubjectResource($subject);
    }

    /**
     * Display the specified resource.
     */
    public function show(Subject $subject)
    {
        return new SubjectResource($subject);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateSubjectRequest $request, Subject $subject)
    {
        HistoryLogger::logAction('Обновлен предмет', $subject->toArray());
        $subject->update($request->all());

        return new SubjectResource($subject);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Subject $subject)
    {
        $subject->delete();

        return response()->noContent();

    }

    public function merge(Request $request)
    {
        $data = $request->validate([
            'subject_ids' => 'required|array|min:2',
            'subject_ids.*' => 'integer|exists:subjects,id',
            'target_name' => 'required|string|max:255',
        ]);

        try {

            DB::transaction(function () use ($data, &$targetSubject) {
                $targetSubject = Subject::firstOrCreate(['name' => $data['target_name']]);

                Lesson::whereIn('subject_id', $data['subject_ids'])
                    ->update(['subject_id' => $targetSubject->id]);

                $teachers = Teacher::whereHas('subjects', function ($query) use ($data) {
                    $query->whereIn('subjects.id', $data['subject_ids']); // Specify 'subjects.id' to avoid ambiguity
                })->get();

                foreach ($teachers as $teacher) {
                    // Detach old subjects and attach the target subject
                    $teacher->subjects()->detach($data['subject_ids']);
                    $teacher->subjects()->syncWithoutDetaching([$targetSubject->id]);
                }

                Subject::whereIn('id', $data['subject_ids'])
                    ->where('id', '<>', $targetSubject->id)
                    ->delete();
            });
            HistoryLogger::logAction('Объеденены предметы в '.$targetSubject->name);
            return response()->json([
                'message' => 'Предметы успешно объеденены.',
                'target_subject' => [
                    'id' => $targetSubject->id,
                    'name' => $targetSubject->name,
                ],
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Ошибка при объеденении предметов.',
                'details' => $e->getMessage(),
            ], 500);
        }

    }
}