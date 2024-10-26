<?php

namespace App\Http\Controllers;

use App\Facades\HistoryLogger;
use App\Http\Requests\StoreBellsPeriodRequest;
use App\Http\Requests\UpdateBellsPeriodRequest;
use App\Http\Resources\BellsPeriodResource;
use App\Models\BellsPeriod;

class BellsPeriodController extends Controller
{
    // Получение всех периодов звонков
    public function index()
    {
        $periods = BellsPeriod::with('bells')->get();

        return BellsPeriodResource::collection($periods);
    }

    // Получение конкретного периода звонка
    public function show($id)
    {
        $period = BellsPeriod::findOrFail($id);

        return new BellsPeriodResource($period);
    }

    // Создание нового периода звонка
    public function store(StoreBellsPeriodRequest $request)
    {
        $period = BellsPeriod::create($request->all());
        HistoryLogger::logAction('Добавлен звонок', $period->toArray());

        return new BellsPeriodResource($period);
    }

    // Обновление периода звонка
    public function update(UpdateBellsPeriodRequest $request, $id)
    {
        $period = BellsPeriod::findOrFail($id);
        HistoryLogger::logAction('Обновлен звонок', $period->toArray());
        $period->update($request->all());

        return new BellsPeriodResource($period);
    }

    // Удаление периода звонка
    public function destroy($id)
    {
        $period = BellsPeriod::findOrFail($id);
        HistoryLogger::logAction('Удален звонок', $period->toArray());
        $period->delete();

        return response()->json(['message' => 'Период звонка удален.']);
    }
}
