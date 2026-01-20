<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Auth\RegisterCompanyController;
use App\Http\Controllers\RequestController;
use App\Http\Controllers\ServiceController;

Route::get('/', [HomeController::class, 'index'])->name('home');

// Authentication Routes
Route::get('/register/company', [RegisterCompanyController::class, 'showRegistrationForm'])->name('register.company');
Route::post('/register/company', [RegisterCompanyController::class, 'handleRegistration'])->name('register.company.handle');

// Login route (placeholder - you'll need to create this)
Route::get('/login', function () {
    return redirect()->route('home');
})->name('login');

// Logout route
Route::post('/logout', function () {
    \Illuminate\Support\Facades\Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect()->route('home')->with('success', __('header.LOGOUT_SUCCESS', [], app()->getLocale()));
})->name('logout')->middleware('auth');

// Dashboard route (placeholder - you'll need to create this)
Route::get('/dashboard', function () {
    return view('dashboard');
})->name('dashboard')->middleware('auth');

// Legal routes (placeholder)
Route::get('/legal/terms', function () {
    return view('legal.terms');
})->name('legal.terms');

Route::get('/legal/privacy', function () {
    return view('legal.privacy');
})->name('legal.privacy');

// Requests Routes
Route::get('/requests/browse', [RequestController::class, 'browse'])->name('requests.browse');

// Services Routes
Route::get('/services/browse', [ServiceController::class, 'browse'])->name('services.browse');
