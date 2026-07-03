<?php

use App\Modules\LearningPath\Http\Controllers\CompletePlanStepController;
use App\Modules\LearningPath\Http\Controllers\GetCurrentPlanController;
use App\Modules\LearningPath\Http\Controllers\GetCurrentStepController;
use App\Modules\LearningPath\Http\Controllers\GetProgressController;
use App\Modules\LearningPath\Http\Controllers\ListTracksController;
use App\Modules\LearningPath\Http\Controllers\StartPlanStepController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->prefix('learning-path')->name('learning-path.')->group(function () {
    Route::get('/tracks', ListTracksController::class)->name('tracks');
    Route::get('/', GetCurrentPlanController::class)->name('current');
    Route::get('/progress', GetProgressController::class)->name('progress');
    Route::get('/current-step', GetCurrentStepController::class)->name('current-step');
    Route::post('/steps/{stepId}/start', StartPlanStepController::class)->name('steps.start');
    Route::post('/steps/{stepId}/complete', CompletePlanStepController::class)->name('steps.complete');
});
