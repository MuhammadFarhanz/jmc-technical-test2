<?php

use App\Http\Controllers\ItemController;
use App\Http\Controllers\KategoriController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SubKategoriController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Public routes
Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

// Authenticated routes
Route::middleware(['auth', 'verified'])->group(function () {

    Route::get('/dashboard', [ItemController::class, 'index'])->name('dashboard');
    Route::put('/items/{id}', [ItemController::class, 'update'])->name('items.update');
    Route::post('/items', [ItemController::class, 'store'])->name('items.store');
    // Route::get('/items', [ItemController::class, 'index'])->name('items.index');

    // Profile routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Kategori CRUD
    Route::prefix('master-data')->group(function () {
        Route::get('/kategori', [KategoriController::class, 'index'])->name('master-data.kategori');
        Route::post('/kategori', [KategoriController::class, 'store'])->name('kategori.store');
        Route::get('/kategori/{id}', [KategoriController::class, 'show'])->name('kategori.show');
        Route::put('/kategori/{id}', [KategoriController::class, 'update'])->name('kategori.update');
        Route::delete('/kategori/{id}', [KategoriController::class, 'destroy'])->name('kategori.destroy');

        // SubKategori CRUD
        Route::get('/sub-kategori', [SubKategoriController::class, 'index']);
        Route::post('/sub-kategori', [SubKategoriController::class, 'store'])->name('subkategori.store');
        Route::get('/sub-kategori/{id}', [SubKategoriController::class, 'show'])->name('subkategori.show');
        Route::put('/sub-kategori/{id}', [SubKategoriController::class, 'update'])->name('subkategori.update');
        Route::delete('/sub-kategori/{id}', [SubKategoriController::class, 'destroy'])->name('subkategori.destroy');
    });

    // User Management
    Route::get('/manajemen-user', [UserController::class, 'index'])->name('manajemen-user');
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
    Route::get('/users/{id}', [UserController::class, 'show'])->name('users.show');
    Route::put('/users/{id}', [UserController::class, 'update'])->name('users.update');
    Route::delete('/users/{id}', [UserController::class, 'destroy'])->name('users.destroy');

});



require __DIR__ . '/auth.php';