<?php

use App\Http\Controllers\ProfileController;
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
});

Route::inertia('/manajemen-user', 'ManajemenUser');
Route::inertia('/master-data/kategori', 'Kategori');
Route::inertia('/master-data/sub-kategori', 'SubKategori');

// Route::get('/manajemen-user', function () {
//     return Inertia::render('ManajemenUser', [
//         // props
//     ]);
// });
// Route::get('/master-data/kategori', function () {
//     return Inertia::render('Kategori', [
//         // props
//     ]);
// });
// Route::get('/master-data/sub-kategori', function () {
//     return Inertia::render('SubKategori', [
//         // props
//     ]);
// });
require __DIR__ . '/auth.php';
