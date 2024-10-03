<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

class HistoryLogger extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'historyLogger';
    }
}
