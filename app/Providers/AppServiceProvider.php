<?php

namespace App\Providers;

use App\Services\HistoryLogger;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Symfony\Component\HttpFoundation\Response;

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

            // if ($request->user()) {
            //     return Limit::none();
            // }
            // return Limit::none();
            // return Limit::perMinute(60)->by($request->ip())->response(function () {
            //     return response()->json([
            //         'message' => 'Превышен лимит запросов, попробуйте позже.',
            //     ], Response::HTTP_TOO_MANY_REQUESTS);
            // });
        });
        RateLimiter::for('login', function (Request $request) {
            return [
                Limit::perMinute(500),
                Limit::perMinute(3)->by($request->input('email'))->response(function () {
                    return response()->json([
                        'message' => 'Превышено количество попыток входа. Пожалуйста, попробуйте снова через минуту.',
                    ], Response::HTTP_TOO_MANY_REQUESTS);
                }),
            ];
        });

        $this->app->singleton('historyLogger', function ($app) {
            return new HistoryLogger();
        });

        JsonResource::withoutWrapping();
    }
}
