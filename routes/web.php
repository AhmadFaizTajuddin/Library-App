<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LoanController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('books.index'));

// Public catalogue. BookController protects create/edit/delete with auth + admin middleware.
Route::resource('books', BookController::class);

// Authentication
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:6,1');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Loans (all logged-in users)
    Route::get('/loans/mine', [LoanController::class, 'mine'])->name('loans.mine');
    Route::post('/loans', [LoanController::class, 'store'])->name('loans.store');
    Route::patch('/loans/{loan}/return', [LoanController::class, 'returnBook'])->name('loans.return');

    // Administrator only
    Route::middleware('admin')->group(function () {
        Route::resource('categories', CategoryController::class)->except('show');
        Route::get('/loans', [LoanController::class, 'index'])->name('loans.index');
        Route::delete('/loans/{loan}', [LoanController::class, 'destroy'])->name('loans.destroy');
    });
});
