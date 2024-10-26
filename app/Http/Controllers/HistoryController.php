<?php

namespace App\Http\Controllers;

use App\Http\Resources\HistoryResource;
use App\Models\History;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class HistoryController extends Controller
{
    public function index(Request $request)
    {
        $query = History::query();

        // Фильтр по строке поиска
        if ($request->has('search') && ! empty($request->input('search'))) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('action', 'ilike', '%'.$search.'%')
                    ->orWhere('details', 'ilike', '%'.$search.'%')
                    ->orWhere('id', 'ilike', '%'.$search.'%');
            });
        }

        // Фильтр по дате
        if ($request->has('start_date') && $request->has('end_date')) {
            $startDate = Carbon::parse($request->input('start_date'))->startOfDay();
            $endDate = Carbon::parse($request->input('end_date'))->endOfDay();
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }

        // Пагинация
        $histories = $query->orderBy('id', 'desc')->paginate(10); // Здесь пагинация на 10 записей

        return HistoryResource::collection($histories);
    }

    public function destroy(History $history)
    {
        $history->delete();

        return response()->noContent();
    }
}
