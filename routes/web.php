<?php

use App\Http\Controllers\ClientController;
use App\Http\Controllers\DiscoverKeywordsController;
use App\Http\Controllers\KeywordController;
use App\Http\Controllers\KeywordPlannerController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\NegativeKeywordController;
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

    Route::post('/clients', [ClientController::class, 'store'])->name('clients.store');
    Route::patch('/clients/{client}', [ClientController::class, 'update'])->name('clients.update');
    Route::delete('/clients/{client}', [ClientController::class, 'destroy'])->name('clients.destroy');

    Route::prefix('keywords')->name('keywords.')->group(function () {
        Route::get('/', [KeywordController::class, 'index'])->name('index');
        Route::get('/{client}', [KeywordController::class, 'show'])->name('show');
        Route::post('/{client}/import', [KeywordController::class, 'import'])->name('import');
        Route::get('/{client}/export', [KeywordController::class, 'export'])->name('export');
    });

    Route::prefix('negative-keywords')->name('negative-keywords.')->group(function () {
        Route::get('/', [NegativeKeywordController::class, 'index'])->name('index');
        Route::get('/{client}', [NegativeKeywordController::class, 'show'])->name('show');
        Route::post('/{client}/import', [NegativeKeywordController::class, 'import'])->name('import');
        Route::get('/{client}/export', [NegativeKeywordController::class, 'export'])->name('export');
    });

    Route::prefix('locations')->name('locations.')->group(function () {
        Route::get('/', [LocationController::class, 'index'])->name('index');
        Route::get('/{client}', [LocationController::class, 'show'])->name('show');
        Route::post('/{client}/import', [LocationController::class, 'import'])->name('import');
        Route::get('/{client}/export', [LocationController::class, 'export'])->name('export');
    });
});

require __DIR__.'/auth.php';
