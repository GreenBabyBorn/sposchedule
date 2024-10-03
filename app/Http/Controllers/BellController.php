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
    public function presetsBells(Request $request)
    {
        return BellsResource::collection(Bell::where('is_preset', true)->get());

    }

    public function applyPreset(Request $request)
    {
        // Получаем звонок по ID
        $preset = Bell::find($request->preset_id);
        $bells = Bell::find($request->bells_id);

        if ($preset && $bells) {
            // Копируем атрибуты $preset в $bells
            // $bells->type = $preset->type;
            // $bells->week_day = $preset->week_day;
            // $bells->date = $preset->date;
            // $bells->building = $preset->building;
            // $bells->is_preset = $preset->is_preset;
            // $bells->name_preset = $preset->name_preset;
            // $bells->name_preset = $preset->name_preset;

            // Сохраняем изменения в $bells
            $bells->save();

            // Очищаем текущие периоды у $bells, если необходимо
            $bells->periods()->delete();

            // Копируем периоды из $preset в $bells
            foreach ($preset->periods as $period) {
                // Создаем новый период для $bells на основе периода $preset
                $newPeriod = $period->replicate(); // Копирует все атрибуты $period
                $newPeriod->bells_id = $bells->id;  // Связываем новый период с новым bell
                $newPeriod->save(); // Сохраняем новый период
            }

            return response()->json([
                'success' => true,
                'message' => 'Пресет и связанные периоды успешно скопированы в объект звонков',
            ]);
        } else {
            return response()->json([
                'error' => 404,
                'message' => 'Пресет или звонок не найден',
            ], 404);
        }
    }


    public function saveAsPreset(Request $request)
    {
        // Получаем звонок по ID
        $bell = Bell::find($request->bells_id);

        if (!$bell) {
            return response()->json([
                'message' => 'Звонок не найден'
            ], 404);
        }

        if ($bell->is_preset) {
            return response()->json([
                'message' => 'Этот звонок уже является пресетом'
            ], 400);
        }

        // Создаем копию звонка с флагом "preset"
        $presetBell = $bell->replicate();
        $presetBell->is_preset = true;
        $presetBell->name_preset = $request->name ?? 'Пресет для звонка ' . $bell->id;
        $presetBell->save();

        // 3. Копируем зависимые записи из таблицы bells_periods
        $bellPeriods = $bell->periods; // Предполагается, что у модели Bell есть отношение periods

        foreach ($bellPeriods as $period) {
            // Копируем период и привязываем его к новому звонку-пресету
            $newPeriod = $period->replicate();
            $newPeriod->bells_id = $presetBell->id; // Привязываем к новому звонку
            $newPeriod->save();
        }

        return response()->json([
            'message' => 'Звонок успешно сохранен как пресет',
            'preset_bell' => new BellsResource($presetBell)
        ]);
    }

    public function publicBells(Request $request)
    {
        $building = $request->input('building');
        $date = $request->input('date');

        if (!$date) {
            return response()->json([
                'message' => 'Необходимо указать дату.',
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

        try {
            $parsedDate = Carbon::parse($date);
            $formattedDate = $parsedDate->format('Y-m-d');
            $weekDay = $weekDayMapping[$parsedDate->dayOfWeek];
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Неверный формат даты.',
            ], 400);
        }

        $changesQuery = Bell::where('type', 'changes')
            ->whereDate('date', $formattedDate)
            ->where('published', true)
            ->where('is_preset', false);

        if ($building) {
            $changesQuery->where('building', $building);
        }

        $changes = $changesQuery->get();

        $mainQuery = Bell::where('type', 'main')
            ->where('week_day', $weekDay)
            ->where('published', true)
            ->where('is_preset', false);


        if ($building) {
            $mainQuery->where('building', $building);
        }

        $mainBells = $mainQuery->get();

        $bells = $changes->merge($mainBells);

        $filteredBells = $bells->sortByDesc(function ($bell) {
            return $bell->type === 'changes' ? 1 : 0;
        })->unique('building')->sortBy('building');

        if ($filteredBells->isEmpty()) {
            return response()->json([
                'message' => 'Расписание не найдено для указанного здания и даты.',
            ], 404);
        }


        return BellsResource::collection($filteredBells);
    }
    public function publicBellsPrint(Request $request)
    {
        $buildings = $request->input('buildings');
        $date = $request->input('date');

        if (!$date) {
            return response()->json([
                'message' => 'Необходимо указать дату.',
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

        try {
            $parsedDate = Carbon::parse($date);
            $formattedDate = $parsedDate->format('Y-m-d');
            $weekDay = $weekDayMapping[$parsedDate->dayOfWeek];
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Неверный формат даты.',
            ], 400);
        }

        // Разделяем строку buildings на массив, если передано несколько корпусов
        $buildingsArray = $buildings ? explode(',', $buildings) : null;

        // Запрос для изменений (changes)
        $changesQuery = Bell::where('type', 'changes')
            ->whereDate('date', $formattedDate)
            ->where('published', true)
            ->where('is_preset', false);

        if ($buildingsArray) {
            $changesQuery->whereIn('building', $buildingsArray);
        }

        $changes = $changesQuery->get();

        // Запрос для основного расписания (main)
        $mainQuery = Bell::where('type', 'main')
            ->where('week_day', $weekDay)
            ->where('published', true)
            ->where('is_preset', false);

        if ($buildingsArray) {
            $mainQuery->whereIn('building', $buildingsArray);
        }

        $mainBells = $mainQuery->get();

        // Объединяем и фильтруем результаты
        $bells = $changes->merge($mainBells);

        $filteredBells = $bells->sortByDesc(function ($bell) {
            return $bell->type === 'changes' ? 1 : 0;
        })->unique('building')->sortBy('building');

        if ($filteredBells->isEmpty()) {
            return response()->json([
                'message' => 'Расписание не найдено для указанных зданий и даты.',
            ], 404);
        }

        return BellsResource::collection($filteredBells);
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

        // $validator = Validator::make($request->all(), [
        //     'type' => 'required|in:main,changes',
        //     'variant' => 'required|in:normal,reduced',
        //     'week_day' => 'nullable|string|max:2|required_if:type,main',
        //     'date' => 'nullable|date|required_if:type,changes',
        // ]);

        // if ($validator->fails()) {
        //     return response()->json(['errors' => $validator->errors()], 422);
        // }

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
