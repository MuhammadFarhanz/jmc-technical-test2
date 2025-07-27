<?php

use App\Http\Controllers\ItemController;
use App\Http\Controllers\KategoriController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Kategori CRUD
    Route::get('/kategori', [KategoriController::class, 'index'])->name('kategori.index');
    Route::post('/kategori', [KategoriController::class, 'store'])->name('kategori.store');
    Route::get('/kategori/{id}', [KategoriController::class, 'show'])->name('kategori.show');
    Route::put('/kategori/{id}', [KategoriController::class, 'update'])->name('kategori.update');
    Route::delete('/kategori/{id}', [KategoriController::class, 'destroy'])->name('kategori.destroy');

    // SubKategori CRUD
    Route::get('/sub-kategori', [\App\Http\Controllers\SubKategoriController::class, 'index'])->name('subkategori.index');
    Route::post('/sub-kategori', [\App\Http\Controllers\SubKategoriController::class, 'store'])->name('subkategori.store');
    Route::get('/sub-kategori/{id}', [\App\Http\Controllers\SubKategoriController::class, 'show'])->name('subkategori.show');
    Route::put('/sub-kategori/{id}', [\App\Http\Controllers\SubKategoriController::class, 'update'])->name('subkategori.update');
    Route::delete('/sub-kategori/{id}', [\App\Http\Controllers\SubKategoriController::class, 'destroy'])->name('subkategori.destroy');

    // User CRUD
    Route::get('/users', [\App\Http\Controllers\UserController::class, 'index'])->name('users.index');
    Route::post('/users', [\App\Http\Controllers\UserController::class, 'store'])->name('users.store');
    Route::get('/users/{id}', [\App\Http\Controllers\UserController::class, 'show'])->name('users.show');
    Route::put('/users/{id}', [\App\Http\Controllers\UserController::class, 'update'])->name('users.update');
    Route::delete('/users/{id}', [\App\Http\Controllers\UserController::class, 'destroy'])->name('users.destroy');
    

    // Item CRUD
    Route::post('/items', [ItemController::class, 'store'])->name('items.store');
    Route::get('/items', [ItemController::class, 'index']);
});

Route::get('/user/current', [UserController::class, 'current'])->middleware('auth:sanctum');
Route::get('/users/operators', [UserController::class, 'operators']);

Route::inertia('/manajemen-user', 'ManajemenUser');
Route::inertia('/master-data/kategori', 'Kategori');
Route::inertia('/master-data/sub-kategori', 'SubKategori');

require __DIR__ . '/auth.php';
