<?php

use App\Http\Controllers\ChirpController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
    Route::get('chirps', [ChirpController::class, 'index'])->name('chirps');
});

Route::middleware(['auth'])->group(function () {
    Route::view('profile', 'profile')->name('profile');
});

require __DIR__.'/auth.php';
