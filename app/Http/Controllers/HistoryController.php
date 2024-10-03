<?php

namespace App\Http\Controllers;

use App\Http\Resources\HistoryResource;
use App\Models\History;
use Illuminate\Http\Request;

class HistoryController extends Controller
{
    public function index()
    {
        return HistoryResource::collection(History::orderBy('id', 'desc')->get());
    }
    public function destroy(History $history)
    {
        $history->delete();
        return response()->noContent();
    }
}
