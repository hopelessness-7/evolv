<?php

use App\Modules\Learn\Http\Controllers\GetCurrentLessonController;
use App\Modules\Learn\Http\Controllers\GetTodayController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->prefix('learn')->name('learn.')->group(function () {
    Route::get('/today', GetTodayController::class)->name('today');
    Route::get('/current-lesson', GetCurrentLessonController::class)->name('current-lesson');
});
