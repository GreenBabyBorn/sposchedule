<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Resources\BellsResource;
use App\Models\Bell;
use App\Models\BellPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\Builder;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Support\Carbon;
use App\Models\BellsPeriod;

class BellController extends Controller
{
    public function index(Request $request)
    {
        $queryParams = $request->only(['type', 'week_day', 'date', 'building']);

        // Валидация входных параметров
        $validator = Validator::make($queryParams, [
            'type' => 'required|string|in:main,changes',
            // 'variant' => 'required|string',
            'week_day' => 'nullable|string',
            'date' => 'nullable|date',
            'building' => 'nullable|integer|between:1,6', // Валидация параметра building
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        try {
            // Поиск записи на основе переданных параметров
            $query = Bell::query();

            // Добавляем фильтрацию по building, если он передан
            if (!empty($queryParams['building'])) {
                $query->where('building', $queryParams['building']);
            }

            // Фильтрация по типу main
            if ($queryParams['type'] === 'main') {
                $query->where('week_day', $queryParams['week_day']);
            }
            // Фильтрация по типу changes
            elseif ($queryParams['type'] === 'changes') {
                if (!empty($queryParams['date'])) {
                    $carbonDate = Carbon::parse($queryParams['date']); // Преобразуем строку даты в объект Carbon
                    $query->whereDate('date', $carbonDate->format('Y-m-d')); // Учитываем только дату без времени

                }
            }
            // Фильтрация по остальным типам
            else {
                $carbonDate = Carbon::parse($queryParams['date']);

                $query->where(function (Builder $query) use ($queryParams, $carbonDate) {
                    $query->whereDate('date', $carbonDate->format('Y-m-d'))
                          ->orWhere('week_day', $queryParams['week_day']);
                })
                ->whereDoesntHave('self', function (Builder $query) use ($queryParams, $carbonDate) {
                    $query->where('variant', $queryParams['variant'])
                          ->whereDate('date', $carbonDate->format('Y-m-d'));
                });
            }

            // Выполняем запрос
            $bells = $query->firstOrFail();

        } catch (\Exception $e) {
            throw new NotFoundHttpException('Запись не найдена');
        }

        return new BellsResource($bells);
    }

    public function publicBells(Request $request)
    {
        // Получаем параметры `building` и `date` из запроса
        $building = $request->input('building');
        $date = $request->input('date');

        // Проверяем, что оба параметра переданы
        if (!$building || !$date) {
            return response()->json([
                'message' => 'Необходимо указать building и date.',
            ], 400);
        }
        $weekDayMapping = [
            0 => 'ВС',
            1 => 'ПН',
            2 => 'ВТ',
            3 => 'СР',
            4 => 'ЧТ',
            5 => 'ПТ',
            6 => 'СБ',
        ];
        // Форматируем дату
        try {
            $parsedDate = Carbon::parse($date);
            $formattedDate = $parsedDate->format('d.m.Y');
            $weekDay = $weekDayMapping[$parsedDate->dayOfWeek];
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Неверный формат даты.',
            ], 400);
        }

        // Ищем измененное расписание (type=changes)
        $schedule = Bell::where('building', $building)
                ->whereDate('date', $formattedDate)
       ->where('type', 'changes')

          ->first();

        // Если измененное расписание не найдено, ищем основное расписание (type=main)
        if (!$schedule) {
            $schedule = Bell::where('building', $building)
            ->where('type', 'main')
              ->where('week_day', $weekDay)

              ->first();
        }

        // Проверяем, есть ли записи
        if (!$schedule) {
            return response()->json([
                'message' => 'Расписание не найдено для указанного здания и даты.',
            ], 404);
        }

        // Возвращаем данные через Resource
        return new BellsResource($schedule);
    }
    // Получение конкретного расписания звонков
    public function show($id)
    {
        $bell = Bell::with('periods')->findOrFail($id);
        return response()->json($bell);
    }

    // Создание нового расписания звонков
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|in:main,changes',
            // 'variant' => 'required|in:normal,reduced',
            'week_day' => 'nullable|string|max:2|required_if:type,main',
            'date' => 'nullable|date|required_if:type,changes',
            'building' => 'integer|between:1,6',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $bell = Bell::create($request->all());
        return response()->json($bell, 201);
    }

    // Обновление расписания звонков
    public function update(Request $request, $id)
    {
        $bell = Bell::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'type' => 'required|in:main,changes',
            'variant' => 'required|in:normal,reduced',
            'week_day' => 'nullable|string|max:2|required_if:type,main',
            'date' => 'nullable|date|required_if:type,changes',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $bell->update($request->all());
        return response()->json($bell);
    }

    // Удаление расписания звонков
    public function destroy($id)
    {
        $bell = Bell::findOrFail($id);
        $bell->delete();
        return response()->json(['message' => 'Расписание звонков удалено.']);
    }
}