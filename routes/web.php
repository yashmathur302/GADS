<?php

use App\Http\Controllers\DiscoverKeywordsController;
use App\Http\Controllers\KeywordPlannerController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/discover');

Route::middleware('auth')->group(function () {
    Route::get('/discover', [DiscoverKeywordsController::class, 'index'])->name('discover.index');
    Route::post('/discover', [DiscoverKeywordsController::class, 'search'])->name('discover.search');

    Route::get('/planner', [KeywordPlannerController::class, 'index'])->name('planner.index');
    Route::post('/planner', [KeywordPlannerController::class, 'forecast'])->name('planner.forecast');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
});

require __DIR__.'/auth.php';
