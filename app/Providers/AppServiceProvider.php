<?php

namespace App\Providers;

use App\Models\History;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\ServiceProvider;
use App\Services\HistoryLogger;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('api', function (Request $request) {
            // Если пользователь авторизован, не применяем ограничение
            if ($request->user()) {
                return Limit::none(); // Отключает ограничение для авторизованного пользователя
            }
            $key = $request->ip() . '|' . $request->header('User-Agent');
            // Применяем ограничение для неавторизованных пользователей
            return Limit::perMinute(60)->by($key);
        });

        $this->app->singleton('historyLogger', function ($app) {
            return new HistoryLogger();
        });

        JsonResource::withoutWrapping();
    }
}