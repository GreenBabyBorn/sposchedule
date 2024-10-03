<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Resources\BellsPeriodResource;
use App\Models\BellsPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Facades\HistoryLogger;

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
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'bells_id' => 'required|exists:bells,id',
            'index' => 'required|integer|min:0',
            'has_break' => 'required|boolean',
            'period_from' => 'required|date_format:H:i',
            'period_to' => 'required|date_format:H:i',
            'period_from_after' => 'nullable|date_format:H:i|required_if:has_break,true',
            'period_to_after' => 'nullable|date_format:H:i|required_if:has_break,true',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $period = BellsPeriod::create($request->all());
        HistoryLogger::logAction('Добавлен звонок', $period->toArray());
        return new BellsPeriodResource($period);
    }

    // Обновление периода звонка
    public function update(Request $request, $id)
    {
        $period = BellsPeriod::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'bells_id' => 'required|exists:bells,id',
            'index' => 'required|integer|min:0',
            'has_break' => 'required|boolean',
            'period_from' => 'required|date_format:H:i',
            'period_to' => 'required|date_format:H:i',
            'period_from_after' => 'nullable|date_format:H:i|required_if:has_break,true',
            'period_to_after' => 'nullable|date_format:H:i|required_if:has_break,true',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
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
