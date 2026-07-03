<?php

use App\Modules\Content\Http\Controllers\CheckQuizController;
use App\Modules\Content\Http\Controllers\ShowNodeContentController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->prefix('content')->name('content.')->group(function () {
    Route::get('/nodes/{slug}', ShowNodeContentController::class)->name('nodes.show');
    Route::post('/nodes/{slug}/quiz-check', CheckQuizController::class)->name('nodes.quiz-check');
});
