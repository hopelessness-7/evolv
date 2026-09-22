<?php

use App\Modules\Coach\Http\Controllers\GetDailyCheckInController;
use App\Modules\Coach\Http\Controllers\GetDailyPlanController;
use App\Modules\Coach\Http\Controllers\StoreDailyCheckInController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->prefix('coach')->name('coach.')->group(function () {
    Route::get('/daily-plan', GetDailyPlanController::class)->name('daily-plan');
    Route::get('/check-ins', GetDailyCheckInController::class)->name('check-ins.show');
    Route::post('/check-ins', StoreDailyCheckInController::class)->name('check-ins.store');
});
