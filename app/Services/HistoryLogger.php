<?php

namespace App\Services;

use App\Models\History;
use Illuminate\Support\Facades\Auth;

class HistoryLogger
{
    /**
     * Логирует действие пользователя.
     */
    public function logAction(string $action, array $details = [], ?int $userId = null): History
    {
        return History::create([
            'user_id' => $userId ?? Auth::id(),
            'action' => $action,
            'details' => $details,
        ]);
    }
}
