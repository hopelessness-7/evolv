<?php

use App\Modules\Curriculum\Http\Controllers\EntryNodesController;
use App\Modules\Curriculum\Http\Controllers\ListNodePrerequisitesController;
use App\Modules\Curriculum\Http\Controllers\ListNodeRelatedController;
use App\Modules\Curriculum\Http\Controllers\ListNodesController;
use App\Modules\Curriculum\Http\Controllers\ShowNodeController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->prefix('curriculum')->name('curriculum.')->group(function () {
    Route::get('/entry-nodes', EntryNodesController::class)->name('entry-nodes');
    Route::get('/nodes', ListNodesController::class)->name('nodes.index');
    Route::get('/nodes/{slug}/prerequisites', ListNodePrerequisitesController::class)->name('nodes.prerequisites');
    Route::get('/nodes/{slug}/related', ListNodeRelatedController::class)->name('nodes.related');
    Route::get('/nodes/{slug}', ShowNodeController::class)->name('nodes.show');
});
