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

Route::post('/login', [AuthController::class, 'login']);
// Route::post('/register', [AuthController::class, 'register']);
Route::get('/schedules/public', [ScheduleController::class, 'getPublicSchedules']);


Route::get('/groups/courses', [GroupController::class, 'getCourses']);
Route::get('/groups/{group}/semester/{semester}/schedules/main', [GroupController::class, 'scheduleMain']);
Route::get('/schedules/changes', [ScheduleController::class, 'getScheduleByDate']);
Route::apiResource('groups', GroupController::class)->only(['index', 'show']);
Route::apiResource('lessons', LessonController::class)->only(['index', 'show']);
Route::apiResource('schedules', ScheduleController::class)->only(['index', 'show']);
Route::apiResource('subjects', SubjectController::class)->only(['index', 'show']);
Route::apiResource('teachers', TeacherController::class)->only(['index', 'show']);
Route::apiResource('semesters', SemesterController::class)->only(['index', 'show']);

/**
 * TODO: Доделать и раскидать только те маршруты, которые требуют аутентификации
 */
Route::get('/bells/public', [BellController::class, 'publicBells']);
Route::apiResource('bells', BellController::class);
Route::apiResource('bells-periods', BellsPeriodController::class);

Route::apiResource('buildings', BuildingController::class)->parameters([
    'buildings' => 'name'
]);

// Маршруты, требующие аутентификации
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::post('/groups/{group}/semesters', [GroupController::class, 'attachSemester'])->where(['semester' => '[0-9]+']);
    Route::delete('/groups/{group}/semesters', [GroupController::class, 'detachSemester'])->where(['semester' => '[0-9]+']);
    Route::apiResource('groups', GroupController::class)->except(['index', 'show']);

    Route::post('/lessons/{lesson}/teachers', [LessonController::class, 'attachTeacher'])->where(['lesson' => '[0-9]+']);
    Route::delete('/lessons/{lesson}/teachers', [LessonController::class, 'detachTeacher'])->where(['lesson' => '[0-9]+']);
    Route::apiResource('lessons', LessonController::class)->except(['index', 'show']);

    Route::patch('/schedules/{schedule}/changes', [ScheduleController::class, 'fromMainToChangesSchedule']);
    Route::apiResource('schedules', ScheduleController::class)->except(['index', 'show']);

    Route::apiResource('subjects', SubjectController::class)->except(['index', 'show']);

    Route::post('/teachers/{teacher}/subjects', [TeacherController::class, 'attachSubject'])->where(['teacher' => '[0-9]+']);
    Route::delete('/teachers/{teacher}/subjects', [TeacherController::class, 'detachSubject'])->where(['teacher' => '[0-9]+']);
    Route::apiResource('teachers', TeacherController::class)->except(['index', 'show']);

    Route::apiResource('semesters', SemesterController::class)->except(['index', 'show']);
});
