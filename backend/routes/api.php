<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BellController;
use App\Http\Controllers\BellsPeriodController;
use App\Http\Controllers\BuildingController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\HistoryController;
use App\Http\Controllers\LessonController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\SemesterController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\LoadController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('throttle:api')->group(function () {
    Route::post('/login', [AuthController::class, 'login'])->name('login')->middleware('throttle:login');

    // Route::post('/register', [AuthController::class, 'register']);
    Route::get('/schedules/public', [ScheduleController::class, 'getPublicSchedules']);

    Route::get('/groups/schedules/hours', [ScheduleController::class, 'getLessonCountsByDateRange']);
    Route::get('/groups/schedules/range', [ScheduleController::class, 'getSchedulesByDateRange']);

    Route::get('/bells/public', [BellController::class, 'publicBells']);
    Route::get('/bells/public/print', [BellController::class, 'publicBellsPrint']);
    Route::get('/bells/presets', [BellController::class, 'presetsBells']);
    Route::apiResource('bells', BellController::class)->only(['index', 'show']);
    Route::apiResource('bells-periods', BellsPeriodController::class)->only(['index', 'show']);
    Route::get('/schedules/changes/print', [ScheduleController::class, 'getChangesSchedulesPrint']);
    Route::get('/schedules/main/semester/{semester}/print', [ScheduleController::class, 'getSchedulesMainPrint']);
    Route::get('/groups/{group}/schedule', [ScheduleController::class, 'getScheduleForGroupAndDate']);

    Route::get('/groups/courses', [GroupController::class, 'getCourses']);
    Route::get('/groups/public', [GroupController::class, 'indexPublic']);
    Route::get('/groups/{group}/semester/{semester}/schedules/main', [GroupController::class, 'scheduleMain']);
    Route::get('/schedules/changes', [ScheduleController::class, 'getChangesSchedules']);
    Route::apiResource('groups', GroupController::class)->only(['index', 'show']);
    Route::apiResource('lessons', LessonController::class)->only(['show']);
    Route::apiResource('schedules', ScheduleController::class)->only(['show']);
    Route::get('/subjects/schedules/main', [ScheduleController::class, 'getMainScheduleSubjects']);
    Route::get('/teachers/schedules/main', [ScheduleController::class, 'getMainScheduleTeachers']);
    Route::apiResource('subjects', SubjectController::class)->only(['index', 'show']);
    Route::apiResource('teachers', TeacherController::class)->only(['index', 'show']);
    Route::apiResource('semesters', SemesterController::class)->only(['index', 'show']);

    Route::apiResource('buildings', BuildingController::class)->parameters([
        'buildings' => 'name',
    ]);

    Route::apiResource('loads', LoadController::class)->only(['index', 'show']);
});

// Маршруты, требующие аутентификации
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::patch('/user', [AuthController::class, 'updateProfile']);

    Route::get('/history', [HistoryController::class, 'index']);
    Route::delete('/history/{history}', [HistoryController::class, 'destroy']);

    Route::post('/bells/presets', [BellController::class, 'saveAsPreset']);
    Route::post('/bells/presets/apply', [BellController::class, 'applyPreset']);
    Route::apiResource('bells', BellController::class)->except(['index', 'show']);
    Route::apiResource('bells-periods', BellsPeriodController::class)->except(['index', 'show']);

    Route::apiResource('groups', GroupController::class)->except(['index', 'show']);

    Route::apiResource('lessons', LessonController::class)->except(['index', 'show']);

    Route::patch('/schedules/{schedule}/lessons/{lesson}', [LessonController::class, 'updateScheduleLesson']);
    Route::patch('/schedules/{schedule}/changes', [ScheduleController::class, 'fromMainToChangesSchedule']);
    Route::post('/schedules/changes', [ScheduleController::class, 'createScheduleWithChanges']);
    Route::apiResource('schedules', ScheduleController::class)->except(['index', 'show']);

    Route::post('/subjects/merge', [SubjectController::class, 'merge']);
    Route::apiResource('subjects', SubjectController::class)->except(['index', 'show']);
    Route::post('/teachers/merge', [TeacherController::class, 'mergeTeachers']);
    Route::apiResource('teachers', TeacherController::class)->except(['index', 'show']);

    Route::apiResource('semesters', SemesterController::class)->except(['index', 'show']);

    Route::apiResource('loads', LoadController::class)->except(['index', 'show']);
});
