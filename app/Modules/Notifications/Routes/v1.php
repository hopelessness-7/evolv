<?php

use App\Modules\Notifications\Http\Controllers\GetNotificationPreferencesController;
use App\Modules\Notifications\Http\Controllers\ListNotificationsController;
use App\Modules\Notifications\Http\Controllers\ShowNotificationController;
use App\Modules\Notifications\Http\Controllers\UpdateNotificationPreferencesController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->prefix('notifications')->name('notifications.')->group(function () {
    Route::get('/', ListNotificationsController::class)->name('index');
    Route::get('/preferences', GetNotificationPreferencesController::class)->name('preferences.show');
    Route::patch('/preferences', UpdateNotificationPreferencesController::class)->name('preferences.update');
    Route::get('/{notificationId}', ShowNotificationController::class)->name('show');
});
