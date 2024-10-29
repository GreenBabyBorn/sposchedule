<?php

namespace App\Http\Controllers;

use App\Facades\HistoryLogger;
use App\Http\Requests\Subject\StoreSubjectRequest;
use App\Http\Requests\Subject\UpdateSubjectRequest;
use App\Http\Resources\SubjectResource;
use Illuminate\Http\Request;
use App\Models\Subject;
use App\Models\Lesson;
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
        // Validate request inputs
        $data = $request->validate([
            'subject_ids' => 'required|array|min:2',
            'subject_ids.*' => 'integer|exists:subjects,id',
            'target_name' => 'required|string|max:255',
        ]);

        try {
            // Initialize targetSubject variable

            DB::transaction(function () use ($data, &$targetSubject) {
                // Find or create the target subject by name
                $targetSubject = Subject::firstOrCreate(['name' => $data['target_name']]);

                // Update lessons to use the target subject ID
                Lesson::whereIn('subject_id', $data['subject_ids'])
                    ->update(['subject_id' => $targetSubject->id]);

                // Delete old subjects, excluding the target subject
                Subject::whereIn('id', $data['subject_ids'])
                    ->where('id', '<>', $targetSubject->id)
                    ->delete();
            });

            return response()->json([
                'message' => 'Subjects merged successfully.',
                'target_subject' => [
                    'id' => $targetSubject->id,
                    'name' => $targetSubject->name,
                ],
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'An error occurred while merging subjects.',
                'details' => $e->getMessage(),
            ], 500);
        }
    }
}