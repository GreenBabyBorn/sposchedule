<?php

use App\Http\Controllers\GroupController;
use App\Http\Controllers\LessonController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\SemesterController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\TeacherController;
use Illuminate\Support\Facades\Route;

Route::post('/groups/{group}/semesters', [GroupController::class, 'attachSemester'])->where(['semester' => '[0-9]+']);
Route::delete('/groups/{group}/semesters', [GroupController::class, 'detachSemester'])->where(['semester' => '[0-9]+']);
Route::apiResource('groups', GroupController::class)->where(['group' => '[0-9]+']);

Route::post('/lessons/{lesson}/teachers', [LessonController::class, 'attachTeacher'])->where(['lesson' => '[0-9]+']);
Route::delete('/lessons/{lesson}/teachers', [LessonController::class, 'detachTeacher'])->where(['lesson' => '[0-9]+']);
Route::apiResource('lessons', LessonController::class)->where(['lesson' => '[0-9]+']);

Route::apiResource('schedules', ScheduleController::class);


Route::apiResource('subjects', SubjectController::class)->where(['subject' => '[0-9]+']);

Route::post('/teachers/{teacher}/subjects', [TeacherController::class, 'attachSubject'])->where(['teacher' => '[0-9]+']);
Route::delete('/teachers/{teacher}/subjects', [TeacherController::class, 'detachSubject'])->where(['teacher' => '[0-9]+']);
Route::apiResource('teachers', TeacherController::class)->where(['teacher' => '[0-9]+']);

Route::apiResource('semesters', SemesterController::class)->where(['semester' => '[0-9]+']);
// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');
