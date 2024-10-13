<?php

namespace App\Providers;

use App\Models\History;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\ServiceProvider;
use App\Services\HistoryLogger;
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

            if ($request->user()) {
                return Limit::none();
            }
            // return Limit::none();
            return Limit::perMinute(60)->by($request->ip())->response(function () {
                return response()->json([
                    'message' => 'Превышен лимит запросов, попробуйте позже.',
                ], Response::HTTP_TOO_MANY_REQUESTS);
            });
        });

        $this->app->singleton('historyLogger', function ($app) {
            return new HistoryLogger();
        });

        JsonResource::withoutWrapping();
    }
}
