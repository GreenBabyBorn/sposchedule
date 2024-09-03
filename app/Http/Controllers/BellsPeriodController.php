<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\BellsPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BellsPeriodController extends Controller
{
    // Получение всех периодов звонков
    public function index()
    {
        $periods = BellsPeriod::with('bells')->get();
        return response()->json($periods);
    }

    // Получение конкретного периода звонка
    public function show($id)
    {
        $period = BellsPeriod::findOrFail($id);
        return response()->json($period);
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
        return response()->json($period, 201);
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

        $period->update($request->all());
        return response()->json($period);
    }

    // Удаление периода звонка
    public function destroy($id)
    {
        $period = BellsPeriod::findOrFail($id);
        $period->delete();
        return response()->json(['message' => 'Период звонка удален.']);
    }
}
