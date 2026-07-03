<?php

use App\Modules\Shared\Http\Controllers\InfraHealthController;
use Illuminate\Support\Facades\Route;

Route::get('/health/infra', InfraHealthController::class)->name('health.infra');
