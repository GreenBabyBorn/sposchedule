<?php

namespace App\Providers;

use App\Services\HistoryLogger;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Validator;
use App\Models\Lesson;

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
        Validator::extend('unique_schedule_index', function ($attribute, $value, $parameters, $validator) {
            $data = $validator->getData();

            if (empty($data['schedule_id']) || !isset($data['index'])) {
                return true;
            }

            $scheduleId = $data['schedule_id'];
            $index = $data['index'];
            $weekType = array_key_exists('week_type', $data) ? $data['week_type'] : null;


            // Check for existing lessons
            $existingLessons = Lesson::where('schedule_id', $scheduleId)
                ->where('index', $index)
                ->get();

            // Check for a conflicting week type
            $hasSameType = $existingLessons->contains(function ($lesson) use ($weekType) {
                return $lesson->week_type === $weekType;
            });

            if ($hasSameType) {
                return false;
            }

            // Allow opposite week types
            if ($weekType === 'ЧИСЛ' || $weekType === 'ЗНАМ') {
                $oppositeType = $weekType === 'ЧИСЛ' ? 'ЗНАМ' : 'ЧИСЛ';
                $oppositeExists = $existingLessons->contains('week_type', $oppositeType);

                return $oppositeExists || $existingLessons->isEmpty();
            }

            return $existingLessons->isEmpty();
        }, 'Невозможно добавить урок: дублирование schedule_id и index.');

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
