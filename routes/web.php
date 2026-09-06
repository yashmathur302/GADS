<?php

use App\Http\Controllers\DiscoverKeywordsController;
use App\Http\Controllers\KeywordPlannerController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

// Not Route::redirect('/', '/discover') — that helper always generates a
// bare root-relative Location header (bypassing URL::forceRootUrl), which
// breaks apps deployed under a subfolder without document-root control.
Route::get('/', fn () => redirect()->route('discover.index'));

Route::middleware('auth')->group(function () {
    Route::get('/discover', [DiscoverKeywordsController::class, 'index'])->name('discover.index');
    Route::post('/discover', [DiscoverKeywordsController::class, 'search'])->name('discover.search');
    Route::post('/discover/export', [DiscoverKeywordsController::class, 'export'])->name('discover.export');

    Route::get('/planner', [KeywordPlannerController::class, 'index'])->name('planner.index');
    Route::post('/planner', [KeywordPlannerController::class, 'forecast'])->name('planner.forecast');
    Route::post('/planner/export', [KeywordPlannerController::class, 'export'])->name('planner.export');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
});

require __DIR__.'/auth.php';
