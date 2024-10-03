<?php

namespace App\Http\Controllers;

use App\Models\History;
use Illuminate\Http\Request;

class HistoryController extends Controller
{
    public function index()
    {
        return History::orderBy('id', 'desc')->get();
    }
    public function destroy(History $history)
    {
        $history->delete();
        return response()->noContent();
    }
}
