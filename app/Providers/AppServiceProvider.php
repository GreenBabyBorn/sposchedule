<?php

namespace App\Providers;

use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\ServiceProvider;

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

            // Применяем ограничение для неавторизованных пользователей
            return Limit::perMinute(60)->by($request->ip());
        });

        JsonResource::withoutWrapping();
    }
}
