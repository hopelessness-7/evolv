<?php

use App\Modules\Practice\Http\Controllers\GetNodeExerciseController;
use App\Modules\Practice\Http\Controllers\SubmitAttemptController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->prefix('practice')->name('practice.')->group(function () {
    Route::get('/nodes/{slug}/exercise', GetNodeExerciseController::class)->name('nodes.exercise');
    Route::post('/nodes/{slug}/attempts', SubmitAttemptController::class)->name('nodes.attempts');
});
