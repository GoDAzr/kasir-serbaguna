<?php

use App\Http\Controllers\Auth\WebAuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ModuleController;
use App\Http\Controllers\PagesController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\TransaksiController;
use Illuminate\Support\Facades\Route;

// ---------- Tamu (belum login) ----------
Route::middleware('guest')->group(function () {
    // "/"      -> halaman login dengan splash screen di atasnya
    // "/login" -> halaman login langsung tanpa splash
    Route::view('/', 'login', ['splash' => true])->name('splash');
    Route::view('/login', 'login')->name('login');

    Route::post('/login', [WebAuthController::class, 'login'])->middleware('throttle:10,1')->name('login.submit');
    Route::post('/register', [WebAuthController::class, 'register'])->middleware('throttle:10,1')->name('register.submit');
});

// Halaman QR publik (meja / takeaway)
Route::get('/qr/{token}', [PagesController::class, 'qr'])->name('qr');

// ---------- Harus login ----------
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/logout', [WebAuthController::class, 'logout'])->name('logout');

    // Akun saya (semua pengguna)
    Route::get('/akun', [PagesController::class, 'akun'])->name('akun');
    Route::post('/akun', [PagesController::class, 'akunSave']);
    Route::get('/akun/password', [PagesController::class, 'password'])->name('akun.password');
    Route::post('/akun/password', [PagesController::class, 'passwordSave']);
    Route::get('/akun/pin', [PagesController::class, 'pin'])->name('akun.pin');
    Route::post('/akun/pin', [PagesController::class, 'pinSave']);
    Route::get('/notifikasi', [PagesController::class, 'notifikasi'])->name('notifikasi');

    // Kasir / POS
    Route::middleware('perm:pos')->group(function () {
        Route::get('/pos', [PosController::class, 'index'])->name('pos');
        Route::post('/pos/checkout', [PosController::class, 'checkout'])->name('pos.checkout');
    });

    // Transaksi, pembatalan, retur
    Route::middleware('perm:transaksi')->group(function () {
        Route::get('/transaksi', [TransaksiController::class, 'index'])->name('transaksi');
        Route::get('/transaksi/{id}', [TransaksiController::class, 'show'])->whereNumber('id')->name('transaksi.show');
        Route::get('/transaksi/{id}/struk', [TransaksiController::class, 'struk'])->whereNumber('id')->name('transaksi.struk');
        Route::post('/transaksi/{id}/batal', [TransaksiController::class, 'batal'])->whereNumber('id')->name('transaksi.batal');
        Route::get('/retur', [TransaksiController::class, 'returList'])->name('retur.list');
        Route::get('/retur/baru', [TransaksiController::class, 'returForm'])->name('retur.form');
        Route::post('/retur', [TransaksiController::class, 'returStore'])->name('retur.store');
    });

    // Pengaturan toko
    Route::middleware('perm:pengaturan')->group(function () {
        Route::get('/toko', [PagesController::class, 'toko'])->name('toko');
        Route::post('/toko', [PagesController::class, 'tokoSave']);
        Route::get('/toko/jam', [PagesController::class, 'jam'])->name('toko.jam');
        Route::post('/toko/jam', [PagesController::class, 'jamSave']);
    });

    Route::get('/keuangan/saldo', [PagesController::class, 'saldoKas'])->middleware('perm:keuangan')->name('saldo');
    Route::get('/sistem/backup/{id}/unduh', [PagesController::class, 'backupDownload'])->whereNumber('id');

    // Laporan (+ export)
    Route::get('/laporan/{type}', [ReportController::class, 'show'])->name('laporan');

    // Menu data (CRUD otomatis dari config/modules.php)
    Route::prefix('m/{module}')->group(function () {
        Route::get('/', [ModuleController::class, 'index'])->name('modules.index');
        Route::get('/create', [ModuleController::class, 'create'])->name('modules.create');
        Route::post('/', [ModuleController::class, 'store'])->name('modules.store');
        Route::get('/{id}', [ModuleController::class, 'show'])->whereNumber('id')->name('modules.show');
        Route::get('/{id}/edit', [ModuleController::class, 'edit'])->whereNumber('id')->name('modules.edit');
        Route::put('/{id}', [ModuleController::class, 'update'])->whereNumber('id')->name('modules.update');
        Route::delete('/{id}', [ModuleController::class, 'destroy'])->whereNumber('id')->name('modules.destroy');
    });
});
