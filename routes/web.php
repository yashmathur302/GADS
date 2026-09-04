<?php

use App\Http\Controllers\KeywordVaultController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');

    // Keyword Vault and Negative Keyword Vault share the same controller,
    // parameterized by vault type — see App\Enums\KeywordVaultType.
    foreach (['keyword-vault' => 'keyword', 'negative-keyword-vault' => 'negative'] as $uriPrefix => $vaultType) {
        $routeName = $vaultType === 'keyword' ? 'assets.keyword-vault' : 'assets.negative-keywords';

        Route::get("assets/{$uriPrefix}", [KeywordVaultController::class, 'index'])
            ->defaults('type', $vaultType)
            ->name($routeName);

        Route::get("assets/{$uriPrefix}/{industry}", [KeywordVaultController::class, 'show'])
            ->defaults('type', $vaultType)
            ->name("{$routeName}.show");

        Route::post("assets/{$uriPrefix}/{industry}", [KeywordVaultController::class, 'store'])
            ->defaults('type', $vaultType)
            ->name("{$routeName}.store");

        Route::delete("assets/{$uriPrefix}/{industry}/{entry}", [KeywordVaultController::class, 'destroy'])
            ->defaults('type', $vaultType)
            ->name("{$routeName}.destroy");

        Route::get("assets/{$uriPrefix}/{industry}/export", [KeywordVaultController::class, 'export'])
            ->defaults('type', $vaultType)
            ->name("{$routeName}.export");

        Route::post("assets/{$uriPrefix}/{industry}/import", [KeywordVaultController::class, 'import'])
            ->defaults('type', $vaultType)
            ->name("{$routeName}.import");
    }

    // Every other sidebar section (config/nav.php) still points at this
    // shared placeholder until it gets real content and a controller.
    $builtRoutes = ['assets.keyword-vault', 'assets.negative-keywords'];

    foreach (config('nav.admin') as $section) {
        foreach ($section['children'] ?? [] as $item) {
            if (in_array($item['route'], $builtRoutes, true)) {
                continue;
            }

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
