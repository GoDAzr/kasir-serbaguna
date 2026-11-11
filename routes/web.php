<?php

use App\Http\Controllers\Auth\WebAuthController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

// ---------- Tamu (belum login) ----------
// Yang sudah login otomatis dialihkan ke /dashboard oleh middleware 'guest'.
Route::middleware('guest')->group(function () {
    // "/"      -> halaman login dengan splash screen di atasnya (splash terangkat, lalu alamat jadi /login)
    // "/login" -> halaman login langsung tanpa splash
    // Nama route 'login' dipakai Laravel untuk mengarahkan user yang belum login.
    Route::view('/', 'login', ['splash' => true])->name('splash');
    Route::view('/login', 'login')->name('login');

    Route::post('/login', [WebAuthController::class, 'login'])
        ->middleware('throttle:10,1')
        ->name('login.submit');

    Route::post('/register', [WebAuthController::class, 'register'])
        ->middleware('throttle:10,1')
        ->name('register.submit');
});

// ---------- Harus login ----------
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/logout', [WebAuthController::class, 'logout'])->name('logout');
});