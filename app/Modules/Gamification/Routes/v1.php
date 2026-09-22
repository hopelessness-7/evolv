<?php

use App\Modules\Gamification\Http\Controllers\GetProfileController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->prefix('gamification')->name('gamification.')->group(function () {
    Route::get('/me', GetProfileController::class)->name('me');
});
