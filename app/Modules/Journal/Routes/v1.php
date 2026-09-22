<?php

use App\Modules\Journal\Http\Controllers\DeleteJournalEntryController;
use App\Modules\Journal\Http\Controllers\ListJournalEntriesController;
use App\Modules\Journal\Http\Controllers\StoreJournalEntryController;
use App\Modules\Journal\Http\Controllers\UpdateJournalEntryController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->prefix('journal')->name('journal.')->group(function () {
    Route::get('/entries', ListJournalEntriesController::class)->name('entries.index');
    Route::post('/entries', StoreJournalEntryController::class)->name('entries.store');
    Route::patch('/entries/{entryId}', UpdateJournalEntryController::class)->name('entries.update');
    Route::delete('/entries/{entryId}', DeleteJournalEntryController::class)->name('entries.destroy');
});
