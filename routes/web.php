<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Auth\RegisterCompanyController;
use App\Http\Controllers\RequestController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\HowItWorksController;
use App\Http\Controllers\SupplierController;

// Home Route
Route::get('/', [HomeController::class, 'index'])->name('home');

// Careers Route
Route::get('/careers', [App\Http\Controllers\CareerController::class, 'index'])->name('careers');

// Blog Route
Route::get('/blog', [App\Http\Controllers\BlogController::class, 'index'])->name('blog');

// Authentication Routes
Route::get('/register/company', [RegisterCompanyController::class, 'showRegistrationForm'])->name('register.company');
Route::post('/register/company', [RegisterCompanyController::class, 'handleRegistration'])->name('register.company.handle');

// About Us Route
Route::get('/about', function () {
    return view('about');
})->name('about');

// Contact Us Route
Route::get('/contact', function () {
    return view('contact');
})->name('contact');

Route::post('/contact', function () {
    // Placeholder for contact form submission
    return back()->with('success', __('contact.CONTACT_SUCCESS_MESSAGE'));
})->name('contact.send');

// Login routes
Route::get('/login', function () {
    return view('auth.login');
})->name('login');

Route::post('/login', function () {
    $credentials = request()->validate([
        'email' => ['required', 'email'],
        'password' => ['required'],
    ]);

    if (\Illuminate\Support\Facades\Auth::attempt($credentials, request()->boolean('remember'))) {
        request()->session()->regenerate();
        return redirect()->intended(route('dashboard'));
    }

    return back()->withErrors([
        'email' => __('auth.ERROR_INVALID_CREDENTIALS', [], app()->getLocale()),
    ])->onlyInput('email');
})->name('login.handle');

// Password reset route (placeholder - you'll need to create this)
Route::get('/forgot-password', function () {
    return view('auth.forgot-password');
})->name('password.request');

// Logout route
Route::post('/logout', function () {
    \Illuminate\Support\Facades\Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect()->route('home')->with('success', __('header.LOGOUT_SUCCESS', [], app()->getLocale()));
})->name('logout')->middleware('auth');

// Dashboard Routes
use App\Http\Controllers\DashboardController;

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/settings', [DashboardController::class, 'settings'])->name('dashboard.settings');
    Route::post('/dashboard/settings', [DashboardController::class, 'updateSettings'])->name('dashboard.settings.update');
    Route::get('/dashboard/projects', [DashboardController::class, 'projects'])->name('dashboard.projects');
    Route::get('/dashboard/team', [DashboardController::class, 'team'])->name('dashboard.team');
    Route::post('/dashboard/team/invite', [DashboardController::class, 'inviteMember'])->name('dashboard.team.invite');
});

// Legal routes (placeholder)
use App\Http\Controllers\LegalController;

// ...

// Legal routes
Route::get('/legal/terms', [LegalController::class, 'terms'])->name('legal.terms');

Route::get('/legal/privacy', [LegalController::class, 'privacy'])->name('legal.privacy');
Route::get('/legal/msa', [LegalController::class, 'msa'])->name('legal.msa');

// Requests Routes
Route::get('/requests/browse', [RequestController::class, 'browse'])->name('requests.browse');

// Services Routes
Route::get('/services/browse', [ServiceController::class, 'browse'])->name('services.browse');
Route::get('/services/{id}', [ServiceController::class, 'show'])->name('services.show');

// How It Works Route
Route::get('/how-it-works', [HowItWorksController::class, 'index'])->name('how-it-works');
Route::get('/how-it-works/benefits', [HowItWorksController::class, 'benefits'])->name('how-it-works.benefits');
Route::get('/how-it-works/pricing', [HowItWorksController::class, 'pricing'])->name('how-it-works.pricing');
Route::get('/how-it-works/faq', [HowItWorksController::class, 'faq'])->name('how-it-works.faq');

// Suppliers Routes
Route::get('/suppliers/browse', [SupplierController::class, 'browse'])->name('suppliers.browse');
Route::get('/suppliers/{id}', [SupplierController::class, 'show'])->name('suppliers.show');

