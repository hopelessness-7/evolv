<?php

use App\Modules\Onboarding\Http\Controllers\CompleteCoreController;
use App\Modules\Onboarding\Http\Controllers\CompleteSessionController;
use App\Modules\Onboarding\Http\Controllers\ListQuestionnairesController;
use App\Modules\Onboarding\Http\Controllers\SaveAnswersController;
use App\Modules\Onboarding\Http\Controllers\ShowQuestionnaireController;
use App\Modules\Onboarding\Http\Controllers\StartSessionController;
use App\Modules\Onboarding\Http\Controllers\StatusController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->prefix('onboarding')->name('onboarding.')->group(function () {
    Route::get('/status', StatusController::class)->name('status');
    Route::get('/questionnaires', ListQuestionnairesController::class)->name('questionnaires.index');
    Route::get('/questionnaires/{code}', ShowQuestionnaireController::class)->name('questionnaires.show');
    Route::post('/core', CompleteCoreController::class)->name('core.complete');
    Route::post('/sessions', StartSessionController::class)->name('sessions.start');
    Route::patch('/sessions/{sessionId}', SaveAnswersController::class)->name('sessions.save');
    Route::post('/sessions/{sessionId}/complete', CompleteSessionController::class)->name('sessions.complete');
});
