<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');

    // Every sidebar section (config/nav.php) currently points at this shared
    // placeholder until it gets real content and a dedicated controller.
    foreach (config('nav.admin') as $section) {
        foreach ($section['children'] ?? [] as $item) {
            Route::get($item['uri'], function () use ($section, $item) {
                return view('pages.placeholder', [
                    'group' => $section['label'],
                    'title' => $item['label'],
                    'description' => $item['description'],
                ]);
            })->name($item['route']);
        }
    }
});

require __DIR__.'/auth.php';
