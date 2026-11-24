<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'home', ['title' => 'Home'])->name('home');
Route::view('/about', 'about', ['title' => 'About'])->name('about');

Route::get('/dashboard', DashboardController::class)->middleware(['auth', 'staff'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('media/{folder}/{file}', MediaController::class)->name('media');
    Route::get('account', AccountController::class)->name('account');
});

Route::middleware(['auth', 'staff'])->group(function () {
    Route::patch('users/{user}/approve', [UserController::class, 'approve'])->name('users.approve');
    Route::resource('customers', CustomerController::class);
    Route::resource('users', UserController::class);
    Route::resource('products', ProductController::class)->except('show');
    Route::resource('sales', SaleController::class)->only(['index', 'create', 'store']);
});

require __DIR__.'/auth.php';
