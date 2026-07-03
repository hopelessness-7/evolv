<?php

use App\Modules\AI\Http\Controllers\GenerateLessonContentController;
use App\Modules\AI\Http\Controllers\LlmPingController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/ai/ping', LlmPingController::class)->name('ai.ping');
    Route::post('/ai/content/nodes/{slug}/generate', GenerateLessonContentController::class)
        ->name('ai.content.generate');
});
