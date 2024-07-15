<?php

use App\Http\Controllers\GroupController;
use App\Http\Controllers\LessonController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\TeacherController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::apiResource('groups', GroupController::class);
Route::apiResource('lessons', LessonController::class);
Route::apiResource('schedules', ScheduleController::class);
Route::apiResource('subjects', SubjectController::class);
Route::apiResource('teachers', TeacherController::class);
Route::post('/teachers/{teacher}/subjects', [TeacherController::class, 'attachSubject']);
Route::delete('/teachers/{teacher}/subjects', [TeacherController::class, 'detachSubject']);
// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');
