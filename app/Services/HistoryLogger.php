<?php

namespace App\Services;

use App\Models\ApiLog;
use App\Models\History;
use Illuminate\Support\Facades\Auth;

class HistoryLogger
{
    /**
    * Логирует действие пользователя.
    *
    * @param string $action
    * @param array $details
    * @param int|null $userId
    * @return History
    */
    public function logAction(string $action, array $details = [], int $userId = null): History
    {
        return History::create([
        'user_id' => $userId ?? Auth::id(),
        'action' => $action,
        'details' => $details,
        ]);
    }
}
