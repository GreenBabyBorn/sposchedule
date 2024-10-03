<?php

namespace App\Http\Controllers;

use App\Http\Requests\Subject\StoreSubjectRequest;
use App\Http\Requests\Subject\UpdateSubjectRequest;
use App\Http\Resources\SubjectResource;
use App\Models\Subject;
use Illuminate\Http\Request;
use App\Facades\HistoryLogger;

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
}
