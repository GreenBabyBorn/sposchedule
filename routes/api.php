<?php

use App\Http\Controllers\GroupController;
use App\Http\Controllers\LessonController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\SemesterController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\BellController;
use App\Http\Controllers\BellsPeriodController;
use App\Http\Controllers\BuildingController;
use App\Http\Controllers\HistoryController;

Route::middleware('throttle:api')->group(function () {
    Route::post('/login', [AuthController::class, 'login'])->name('login')->middleware('throttle:login');
    ;
    // Route::post('/register', [AuthController::class, 'register']);
    Route::get('/schedules/public', [ScheduleController::class, 'getPublicSchedules']);

    Route::get('/groups/schedules/hours', [ScheduleController::class, 'getLessonCountsByDateRange']);
    Route::get('/groups/schedules/range', [ScheduleController::class, 'getSchedulesByDateRange']);

    Route::get('/bells/public', [BellController::class, 'publicBells']);
    Route::get('/bells/public/print', [BellController::class, 'publicBellsPrint']);
    Route::get('/bells/presets', [BellController::class, 'presetsBells']);
    Route::apiResource('bells', BellController::class)->only(['index', 'show']);
    Route::apiResource('bells-periods', BellsPeriodController::class)->only(['index', 'show']);
    Route::get('/schedules/changes/print', [ScheduleController::class, 'getScheduleByDatePrint']);
    Route::get('/schedules/main/semester/{semester}/print', [ScheduleController::class, 'getSchedulesMainPrint']);
    Route::get('/groups/courses', [GroupController::class, 'getCourses']);
    Route::get('/groups/public', [GroupController::class, 'indexPublic']);
    Route::get('/groups/{group}/semester/{semester}/schedules/main', [GroupController::class, 'scheduleMain']);
    Route::get('/schedules/changes', [ScheduleController::class, 'getScheduleByDate']);
    Route::apiResource('groups', GroupController::class)->only(['index', 'show']);
    Route::apiResource('lessons', LessonController::class)->only(['show']);
    Route::apiResource('schedules', ScheduleController::class)->only(['show']);
    Route::apiResource('subjects', SubjectController::class)->only(['index', 'show']);
    Route::apiResource('teachers', TeacherController::class)->only(['index', 'show']);
    Route::apiResource('semesters', SemesterController::class)->only(['index', 'show']);

    Route::apiResource('buildings', BuildingController::class)->parameters([
        'buildings' => 'name'
    ]);
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

    Route::post('/groups/{group}/semesters', [GroupController::class, 'attachSemester'])->where(['semester' => '[0-9]+']);
    Route::delete('/groups/{group}/semesters', [GroupController::class, 'detachSemester'])->where(['semester' => '[0-9]+']);
    Route::apiResource('groups', GroupController::class)->except(['index', 'show']);

    Route::post('/lessons/{lesson}/teachers', [LessonController::class, 'attachTeacher'])->where(['lesson' => '[0-9]+']);
    Route::delete('/lessons/{lesson}/teachers', [LessonController::class, 'detachTeacher'])->where(['lesson' => '[0-9]+']);
    Route::apiResource('lessons', LessonController::class)->except(['index', 'show']);

    Route::patch('/schedules/{schedule}/changes', [ScheduleController::class, 'fromMainToChangesSchedule']);
    Route::post('/schedules/changes', [ScheduleController::class, 'createScheduleWithChanges']);
    Route::apiResource('schedules', ScheduleController::class)->except(['index', 'show']);

    Route::apiResource('subjects', SubjectController::class)->except(['index', 'show']);

    Route::post('/teachers/{teacher}/subjects', [TeacherController::class, 'attachSubject'])->where(['teacher' => '[0-9]+']);
    Route::delete('/teachers/{teacher}/subjects', [TeacherController::class, 'detachSubject'])->where(['teacher' => '[0-9]+']);
    Route::apiResource('teachers', TeacherController::class)->except(['index', 'show']);

    Route::apiResource('semesters', SemesterController::class)->except(['index', 'show']);
});
