<?php

use App\Modules\Coach\Http\Controllers\GetDailyPlanController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->prefix('coach')->name('coach.')->group(function () {
    Route::get('/daily-plan', GetDailyPlanController::class)->name('daily-plan');
});
