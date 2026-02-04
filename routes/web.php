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
Route::get('/register/student', [App\Http\Controllers\Auth\RegisterStudentController::class, 'showRegistrationForm'])->name('register.student');
Route::post('/register/student', [App\Http\Controllers\Auth\RegisterStudentController::class, 'handleRegistration'])->name('register.student.handle');


// About Us Route
Route::get('/about', function () { return view('about'); })->name('about');

// Contact Us Route
Route::get('/contact', function () { return view('contact');})->name('contact');
Route::post('/contact', function () { return back()->with('success', __('contact.CONTACT_SUCCESS_MESSAGE'));})->name('contact.send');

// Login routes
Route::get('/login', function () {return view('auth.login');})->name('login');
Route::post('/login', [App\Http\Controllers\Auth\LoginController::class, 'store'])->name('login.handle');

// Password reset route (placeholder - you'll need to create this)
Route::get('/forgot-password', function () {
    return view('auth.forgot-password');
})->name('password.request');

Route::post('/forgot-password', function() { return back()->with('status', __('auth.PASSWORD_RESET_SENT'));})->name('password.email');
Route::post('/logout', function () {
    \Illuminate\Support\Facades\Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect()->route('home')->with('success', __('header.LOGOUT_SUCCESS', [], app()->getLocale()));
})->name('logout')->middleware('auth');


// Public Expert's Dashboard Routes
Route::get('/dashboard/expert', [ExpertDashboardController::class, 'index'])->name('dashboard.expert');


Route::middleware(['auth'])->group(function () {

    // E X P E R T   A R E A
    Route::middleware(['expert'])->prefix('dashboard/expert')->name('dashboard.expert')->group(function () {
        Route::get('/', [ExpertDashboardController::class, 'index']); // name is dashboard.expert (from prefix/name)
        
        // Fix: Explicitly naming sub-routes to match existing names if needed, or rely on prefix
        // Existing names were: dashboard.expert.availability, etc.
        // Using name() on group adds prefix, so 'availability' becomes 'dashboard.expert.availability'
        
        Route::get('/availability', [ExpertDashboardController::class, 'availability'])->name('.availability');
        Route::post('/availability', [ExpertDashboardController::class, 'availability']);
        
        Route::get('/cv-builder', [ExpertDashboardController::class, 'cvBuilder'])->name('.cv-builder');
        Route::post('/cv-builder', [ExpertDashboardController::class, 'cvBuilder']);
        
        Route::get('/services', [ExpertDashboardController::class, 'services'])->name('.services');
        Route::post('/services', [ExpertDashboardController::class, 'services']);
        Route::delete('/services/{id}', [ExpertDashboardController::class, 'deleteService'])->name('.services.delete');
        
        Route::get('/workbench', [\App\Http\Controllers\Dashboard\Expert\WorkbenchController::class, 'index'])->name('.workbench');
        Route::post('/workbench/action', [\App\Http\Controllers\Dashboard\Expert\WorkbenchController::class, 'action'])->name('.workbench.action');
        
        Route::get('/settings', [ExpertDashboardController::class, 'settings'])->name('.settings');
        Route::post('/settings', [ExpertDashboardController::class, 'settings']);
    });


    // C O M P A N Y   &   C L I E N T   A R E A
    Route::middleware(['company'])->group(function () {
        
        // Company's Dashboard Routes
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/dashboard/tasks', [DashboardController::class, 'tasks'])->name('dashboard.tasks');
        Route::post('/dashboard/tasks/upload', [DashboardController::class, 'uploadTasks'])->name('dashboard.tasks.upload');
        Route::get('/dashboard/settings', [DashboardController::class, 'settings'])->name('dashboard.settings');
        Route::post('/dashboard/settings', [DashboardController::class, 'updateSettings'])->name('dashboard.settings.update');
        Route::get('/dashboard/projects', [DashboardController::class, 'projects'])->name('dashboard.projects');
        Route::get('/dashboard/team', [DashboardController::class, 'team'])->name('dashboard.team');
        Route::post('/dashboard/team/invite', [DashboardController::class, 'inviteMember'])->name('dashboard.team.invite');

        // Client Governance Dashboard
        Route::get('/client/governance', [\App\Http\Controllers\Client\GovernanceDashboardController::class, 'index'])->name('client.governance.dashboard');
        Route::post('/client/governance/analyze', [\App\Http\Controllers\Client\GovernanceDashboardController::class, 'analyzeCsv'])->name('client.governance.analyze');
        Route::post('/client/governance/upload', [\App\Http\Controllers\Client\GovernanceDashboardController::class, 'uploadTasks'])->name('client.governance.upload');
        Route::put('/client/governance/tasks/{id}', [\App\Http\Controllers\Client\GovernanceDashboardController::class, 'updateTask'])->name('client.governance.task.update');
        Route::delete('/client/governance/tasks/{id}', [\App\Http\Controllers\Client\GovernanceDashboardController::class, 'deleteTask'])->name('client.governance.task.delete');
        Route::post('/client/governance/tasks/{id}/duplicate', [\App\Http\Controllers\Client\GovernanceDashboardController::class, 'duplicateTask'])->name('client.governance.task.duplicate');
        
        Route::get('/client/dashboard/metrics', [\App\Http\Controllers\ClientDashboardController::class, 'getMetrics'])->name('client.dashboard.metrics');
    });

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
