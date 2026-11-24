<?php

use App\Http\Controllers\AccountRegistrationController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store']);
    Route::get('staff/login', fn () => view('auth.login', ['accountType' => 'staff', 'title' => 'Staff sign in']))->name('staff.login');
    Route::get('register', [AccountRegistrationController::class, 'userForm'])->name('register');
    Route::post('register', [AccountRegistrationController::class, 'registerUser'])->middleware('throttle:6,1');
    Route::get('staff/register', [AccountRegistrationController::class, 'staffForm'])->name('staff.register');
    Route::post('staff/register', [AccountRegistrationController::class, 'registerStaff'])->middleware('throttle:6,1');
});
Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->middleware('auth')->name('logout');
