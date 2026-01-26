<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Auth\RegisterCompanyController;
use App\Http\Controllers\RequestController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\HowItWorksController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\Auth\ActivationController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExpertDashboardController;
use App\Http\Controllers\LegalController;

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

Route::post('/forgot-password', function() {
    // Placeholder for password reset email logic
    return back()->with('status', __('auth.PASSWORD_RESET_SENT'));
})->name('password.email');

// Logout route
Route::post('/logout', function () {
    \Illuminate\Support\Facades\Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect()->route('home')->with('success', __('header.LOGOUT_SUCCESS', [], app()->getLocale()));
})->name('logout')->middleware('auth');

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/expert', [ExpertDashboardController::class, 'index'])->name('dashboard.expert');
    Route::get('/dashboard/expert/availability', [ExpertDashboardController::class, 'availability'])->name('dashboard.expert.availability');
    Route::post('/dashboard/expert/availability', [ExpertDashboardController::class, 'availability'])->name('dashboard.expert.availability');
    Route::get('/dashboard/expert/cv-builder', [ExpertDashboardController::class, 'cvBuilder'])->name('dashboard.expert.cv-builder');
    Route::post('/dashboard/expert/cv-builder', [ExpertDashboardController::class, 'cvBuilder'])->name('dashboard.expert.cv-builder');
    Route::get('/dashboard/expert/services', [ExpertDashboardController::class, 'services'])->name('dashboard.expert.services');
    Route::get('/dashboard/expert/workbench', [ExpertDashboardController::class, 'workbench'])->name('dashboard.expert.workbench');
    Route::get('/dashboard/expert/settings', [ExpertDashboardController::class, 'settings'])->name('dashboard.expert.settings');
    Route::post('/dashboard/expert/settings', [ExpertDashboardController::class, 'settings'])->name('dashboard.expert.settings');
    Route::get('/dashboard/settings', [DashboardController::class, 'settings'])->name('dashboard.settings');
    Route::post('/dashboard/settings', [DashboardController::class, 'updateSettings'])->name('dashboard.settings.update');
    Route::get('/dashboard/projects', [DashboardController::class, 'projects'])->name('dashboard.projects');
    Route::get('/dashboard/team', [DashboardController::class, 'team'])->name('dashboard.team');
    Route::post('/dashboard/team/invite', [DashboardController::class, 'inviteMember'])->name('dashboard.team.invite');
});

// Legal routes
Route::get('/legal/terms', [LegalController::class, 'terms'])->name('legal.terms');

Route::get('/legal/privacy', [LegalController::class, 'privacy'])->name('legal.privacy');
Route::get('/legal/msa', [LegalController::class, 'msa'])->name('legal.msa');

// Requests Routes
Route::get('/requests/browse', [RequestController::class, 'browse'])->name('requests.browse');
Route::get('/requests/{id}', [RequestController::class, 'show'])->name('requests.show');
Route::get('/requests/{id}/proposal', [RequestController::class, 'proposal'])->name('requests.proposal');
Route::post('/requests/{id}/proposal', function() {
    return back()->with('success', __('requests.PROPOSAL_SUCCESS_MESSAGE'));
})->name('requests.proposal.send');
Route::get('/requests/{id}/contact', [RequestController::class, 'contact'])->name('requests.contact');
Route::post('/requests/{id}/contact', function() {
    return back()->with('success', __('contact.CONTACT_SUCCESS_MESSAGE'));
})->name('requests.contact.send');

// Services Routes
Route::get('/services/browse', [ServiceController::class, 'browse'])->name('services.browse');
Route::get('/services/{id}', [ServiceController::class, 'show'])->name('services.show');
Route::get('/services/{id}/contact', [ServiceController::class, 'contact'])->name('services.contact');
Route::post('/services/{id}/contact', function() {
    return back()->with('success', __('contact.CONTACT_SUCCESS_MESSAGE')); // Reusing existing message key
})->name('services.contact.send');
Route::get('/services/{id}/request', [ServiceController::class, 'request'])->name('services.request');
Route::post('/services/{id}/request', function() {
    return back()->with('success', __('services.REQUEST_SUCCESS_MESSAGE'));
})->name('services.request.send');

// How It Works Route
Route::get('/how-it-works', [HowItWorksController::class, 'index'])->name('how-it-works');
Route::get('/how-it-works/benefits', [HowItWorksController::class, 'benefits'])->name('how-it-works.benefits');
Route::get('/how-it-works/pricing', [HowItWorksController::class, 'pricing'])->name('how-it-works.pricing');
Route::get('/how-it-works/faq', [HowItWorksController::class, 'faq'])->name('how-it-works.faq');


Route::get('/activate/{id}', [ActivationController::class, 'show'])->name('activation.show')->middleware('signed');
Route::post('/activate/{id}', [ActivationController::class, 'activate'])->name('activation.handle');

// Suppliers Routes
Route::get('/suppliers/browse', [SupplierController::class, 'browse'])->name('suppliers.browse');
Route::get('/suppliers/{id}', [SupplierController::class, 'show'])->name('suppliers.show');

