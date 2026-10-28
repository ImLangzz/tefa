<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ModerationController;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1')->name('login.store');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.store');
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/', [HomeController::class, 'index'])->name('home');

    Route::middleware('role:penjual,pembeli')->group(function () {
        Route::get('/katalog', [ProductController::class, 'index'])->name('products.index');
        Route::get('/katalog/tambah', [ProductController::class, 'create'])->name('products.create');
        Route::post('/katalog/tambah', [ProductController::class, 'store'])->name('products.store');
        Route::get('/katalog/{product}', [ProductController::class, 'show'])->name('products.show');
        Route::post('/produk/{product}/laporkan', [ModerationController::class, 'report'])->name('products.report');
    });

    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/', [ModerationController::class, 'dashboard'])->name('dashboard');
        Route::get('/laporan', [ModerationController::class, 'index'])->name('reports.index');
        Route::get('/produk', [ModerationController::class, 'products'])->name('products.index');
        Route::post('/laporan/{report}/nonaktifkan', [ModerationController::class, 'deactivate'])->name('reports.deactivate');
        Route::post('/laporan/{report}/blacklist', [ModerationController::class, 'blacklist'])->name('reports.blacklist');
        Route::post('/laporan/{report}/abaikan', [ModerationController::class, 'dismiss'])->name('reports.dismiss');
        Route::post('/blacklist/{user}/pulihkan', [ModerationController::class, 'restore'])->name('blacklist.restore');
        Route::post('/produk/{product}/aktifkan', [ModerationController::class, 'activate'])->name('products.activate');
        Route::delete('/produk/{product}', [ModerationController::class, 'deleteProduct'])->name('products.destroy');
    });
});
