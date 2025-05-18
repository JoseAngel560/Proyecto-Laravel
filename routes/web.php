<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Login;
use App\Livewire\NavigationMenu;

// Rutas públicas
Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/login', Login::class)->name('login');

// Rutas protegidas
Route::middleware(['auth.check'])->group(function () {
    Route::get('/dashboard', NavigationMenu::class)->name('dashboard');
    
    Route::get('/logout', function () {
        Session::flush();
        return redirect()->route('login');
    })->name('logout');
});

Route::post('/toggle-dark-mode', [App\Http\Controllers\DarkModeController::class, 'toggle']);

Route::view('/creadores', 'creadores')->name('creadores');

Route::view('/manual-usuario', 'manual-usuario')->name('manual-usuario');



Route::get('/database/download/{filename}', function ($filename) {
    $path = storage_path('app/backups/' . $filename);

    if (!file_exists($path)) {
        abort(404);
    }

    return response()->download($path)->deleteFileAfterSend(true);
})->name('database.download');
