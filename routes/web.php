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
use App\Http\Controllers\ChatController;
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
        Route::get('/', [ExpertDashboardController::class, 'index']); 
        
        Route::get('/availability', [ExpertDashboardController::class, 'availability'])->name('.availability');
        Route::post('/availability', [ExpertDashboardController::class, 'availability']);
        
        Route::get('/cv-builder', [ExpertDashboardController::class, 'cvBuilder'])->name('.cv-builder');
        Route::post('/cv-builder', [ExpertDashboardController::class, 'cvBuilder']);
        
        Route::get('/services', [ExpertDashboardController::class, 'services'])->name('.services');
        Route::post('/services', [ExpertDashboardController::class, 'services']);
        Route::delete('/services/{id}', [ExpertDashboardController::class, 'deleteService'])->name('.services.delete');
        
        Route::get('/workbench', [\App\Http\Controllers\Dashboard\Expert\WorkbenchController::class, 'index'])->name('.workbench');
        Route::post('/workbench/action', [\App\Http\Controllers\Dashboard\Expert\WorkbenchController::class, 'action'])->name('.workbench.action');
        Route::post('/workbench/sentiment', [\App\Http\Controllers\Dashboard\Expert\WorkbenchController::class, 'submitSentiment'])->name('.workbench.sentiment');
        
        Route::get('/settings', [ExpertDashboardController::class, 'settings'])->name('.settings');
        Route::post('/purchase/{id}/accept', [ExpertDashboardController::class, 'acceptPurchase'])->name('.purchase.accept');
        Route::post('/settings', [ExpertDashboardController::class, 'settings']);

        // EXPERT CHAT routes (Prefix: dashboard.expert)
        Route::get('/messages', [ChatController::class, 'index'])->name('.chat.index');
        Route::get('/messages/{id}', [ChatController::class, 'show'])->name('.chat.show');
        Route::post('/messages/{id}/send', [ChatController::class, 'sendMessage'])->name('.chat.send');
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
        Route::get('/dashboard/projects/create', [App\Http\Controllers\ProjectController::class, 'create'])->name('dashboard.projects.create');
        Route::post('/dashboard/projects', [App\Http\Controllers\ProjectController::class, 'store'])->name('dashboard.projects.store');
        Route::get('/dashboard/team', [DashboardController::class, 'team'])->name('dashboard.team');
        Route::post('/dashboard/team/invite', [DashboardController::class, 'inviteMember'])->name('dashboard.team.invite');

        // Client Governance Dashboard
        Route::get('/client/governance', [\App\Http\Controllers\Client\GovernanceDashboardController::class, 'index'])->name('client.governance.dashboard');
        Route::post('/client/governance/analyze', [\App\Http\Controllers\Client\GovernanceDashboardController::class, 'analyzeCsv'])->name('client.governance.analyze');
        Route::post('/client/governance/upload', [\App\Http\Controllers\Client\GovernanceDashboardController::class, 'uploadTasks'])->name('client.governance.upload');
        Route::get('/client/governance/tasks/delete-all', [\App\Http\Controllers\Client\GovernanceDashboardController::class, 'deleteAllTasks'])->name('client.governance.task.delete-all');
        Route::put('/client/governance/tasks/{id}', [\App\Http\Controllers\Client\GovernanceDashboardController::class, 'updateTask'])->name('client.governance.task.update');
        Route::delete('/client/governance/tasks/{id}', [\App\Http\Controllers\Client\GovernanceDashboardController::class, 'deleteTask'])->name('client.governance.task.delete');
        Route::post('/client/governance/tasks/{id}/duplicate', [\App\Http\Controllers\Client\GovernanceDashboardController::class, 'duplicateTask'])->name('client.governance.task.duplicate');
        
        Route::get('/client/dashboard/metrics', [\App\Http\Controllers\ClientDashboardController::class, 'getMetrics'])->name('client.dashboard.metrics');
        Route::get('/client/dashboard/metrics', [\App\Http\Controllers\ClientDashboardController::class, 'getMetrics'])->name('client.dashboard.metrics');

        // COMPANY/CLIENT CHAT routes (Prefix: dashboard)
        Route::get('/dashboard/messages', [ChatController::class, 'index'])->name('dashboard.chat.index');
        Route::get('/dashboard/messages/{id}', [ChatController::class, 'show'])->name('dashboard.chat.show');
        Route::post('/dashboard/messages/{id}/send', [ChatController::class, 'sendMessage'])->name('dashboard.chat.send');
    });

});

// Legal routes
Route::get('/legal/terms', [LegalController::class, 'terms'])->name('legal.terms');
Route::get('/legal/privacy', [LegalController::class, 'privacy'])->name('legal.privacy');
Route::get('/legal/msa', [LegalController::class, 'msa'])->name('legal.msa');

// Debug route to test notifications
Route::get('/test-notifications', function() {
    $user = auth()->user();
    
    if (!$user) {
        return response()->json(['error' => 'Not authenticated']);
    }
    
    return response()->json([
        'user_id' => $user->id,
        'user_name' => $user->name,
        'user_role' => $user->role ?? 'NULL',
        'pending_purchases' => \App\Models\ServicePurchase::where('expert_id', $user->id)
            ->where('status', 'pending')
            ->count(),
        'db_notifications' => $user->notifications()->count(),
        'unread_notifications' => $user->unreadNotifications()->count(),
    ]);
})->middleware('auth');

// Notifications (Web)
Route::prefix('notifications')->name('notifications.')->middleware('auth')->group(function () {
    Route::get('/', [App\Http\Controllers\Api\NotificationController::class, 'index'])->name('index');
    Route::get('/unread-count', [App\Http\Controllers\Api\NotificationController::class, 'unreadCount'])->name('unread-count');
    Route::post('/{id}/read', [App\Http\Controllers\Api\NotificationController::class, 'markAsRead'])->name('read');
    Route::post('/read-all', [App\Http\Controllers\Api\NotificationController::class, 'markAllAsRead'])->name('read-all');
});

// Chat Routes Moved to specific groups

// Requests Routes
Route::get('/requests/browse', [RequestController::class, 'browse'])->name('requests.browse');
Route::get('/requests/{id}', [RequestController::class, 'show'])->name('requests.show');
Route::post('/requests/{id}/offer', [App\Http\Controllers\RequestController::class, 'submitOffer'])->name('requests.offer.submit')->middleware('auth');
Route::get('/requests/{id}/proposal', [RequestController::class, 'proposal'])->name('requests.proposal');
// Proposal Route
Route::post('/requests/{id}/proposal', [App\Http\Controllers\RequestController::class, 'submitOffer'])->name('requests.proposal.submit')->middleware('auth');
Route::post('/requests/offer/{id}/accept', [App\Http\Controllers\RequestController::class, 'acceptOffer'])->name('requests.offer.accept')->middleware('auth');
Route::get('/requests/{id}/contact', [RequestController::class, 'contact'])->name('requests.contact');
Route::post('/requests/{id}/contact', function() {
    return back()->with('success', __('contact.CONTACT_SUCCESS_MESSAGE'));
})->name('requests.contact.send');

// Notifications (Web)
Route::prefix('notifications')->name('notifications.')->middleware('auth')->group(function () {
    Route::get('/', [App\Http\Controllers\Api\NotificationController::class, 'index'])->name('index');
    Route::get('/unread-count', [App\Http\Controllers\Api\NotificationController::class, 'unreadCount'])->name('unread-count');
    Route::post('/{id}/read', [App\Http\Controllers\Api\NotificationController::class, 'markAsRead'])->name('read');
    Route::post('/read-all', [App\Http\Controllers\Api\NotificationController::class, 'markAllAsRead'])->name('read-all');
});

// Services Routes
Route::get('/services/browse', [ServiceController::class, 'browse'])->name('services.browse');
Route::get('/services/{id}', [ServiceController::class, 'show'])->name('services.show');
Route::get('/services/{id}/contact', [ServiceController::class, 'contact'])->name('services.contact');
Route::post('/services/{id}/contact', function() {
    return back()->with('success', __('contact.CONTACT_SUCCESS_MESSAGE')); // Reusing existing message key
})->name('services.contact.send');
Route::get('/services/{id}/request', [ServiceController::class, 'request'])->name('services.request');
Route::post('/services/{id}/request', [ServiceController::class, 'purchaseHours'])->name('services.request.send')->middleware('auth');

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

// Admin Routes
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    // Super Admin Dashboard
    Route::get('/dashboard', [\App\Http\Controllers\Admin\AdminDashboardController::class, 'index'])->name('dashboard');
    
    // User Management
    Route::get('/users', [\App\Http\Controllers\Admin\AdminUserController::class, 'index'])->name('users.index');
    Route::patch('/users/{id}/toggle-status', [\App\Http\Controllers\Admin\AdminUserController::class, 'toggleStatus'])->name('users.toggle-status');
    Route::delete('/users/{id}', [\App\Http\Controllers\Admin\AdminUserController::class, 'destroy'])->name('users.destroy');
    
    // Companies Management
    Route::get('/companies', [\App\Http\Controllers\Admin\AdminCompanyController::class, 'index'])->name('companies.index');
    Route::patch('/companies/{id}/toggle-verified', [\App\Http\Controllers\Admin\AdminCompanyController::class, 'toggleVerified'])->name('companies.toggle-verified');
    Route::delete('/companies/{id}', [\App\Http\Controllers\Admin\AdminCompanyController::class, 'destroy'])->name('companies.destroy');

    // Experts Management
    Route::get('/experts', [\App\Http\Controllers\Admin\AdminExpertController::class, 'index'])->name('experts.index');
    Route::patch('/experts/{id}/toggle-status', [\App\Http\Controllers\Admin\AdminExpertController::class, 'toggleStatus'])->name('experts.toggle-status');
    Route::delete('/experts/{id}', [\App\Http\Controllers\Admin\AdminExpertController::class, 'destroy'])->name('experts.destroy');
    
    // System Settings
    Route::get('/settings', [\App\Http\Controllers\Admin\AdminSettingsController::class, 'index'])->name('settings.index');
    Route::put('/settings', [\App\Http\Controllers\Admin\AdminSettingsController::class, 'update'])->name('settings.update');
    // Dispute Management
    Route::get('/disputes', [App\Http\Controllers\Admin\DisputeController::class, 'index'])->name('disputes.index');
    Route::get('/disputes/{type}/{id}', [App\Http\Controllers\Admin\DisputeController::class, 'show'])->name('disputes.show');
    Route::post('/disputes/{type}/{id}/resolve-company', [App\Http\Controllers\Admin\DisputeController::class, 'resolveForCompany'])->name('disputes.resolve-company');
    Route::post('/disputes/{type}/{id}/resolve-expert', [App\Http\Controllers\Admin\DisputeController::class, 'resolveForExpert'])->name('disputes.resolve-expert');
    Route::post('/disputes/{type}/{id}/message', [App\Http\Controllers\Admin\DisputeController::class, 'sendMessage'])->name('disputes.message');

    // Sentiment Analysis Task Tracker
    Route::get('/sentiment/tasks', [App\Http\Controllers\Admin\SentimentTaskController::class, 'index'])->name('sentiment.index');
    
    // Dataset Upload for Intelligent Routing System
    Route::get('/dataset/upload', [App\Http\Controllers\Dashboard\DatasetUploadController::class, 'index'])->name('dataset.upload');
    Route::post('/dataset/upload', [App\Http\Controllers\Dashboard\DatasetUploadController::class, 'upload'])->name('dataset.upload.store');

    // Services Board
    Route::get('/services', [\App\Http\Controllers\Admin\AdminServiceController::class, 'index'])->name('services.index');
    Route::patch('/services/{id}/toggle-status', [\App\Http\Controllers\Admin\AdminServiceController::class, 'toggleStatus'])->name('services.toggle-status');
    Route::delete('/services/{id}', [\App\Http\Controllers\Admin\AdminServiceController::class, 'destroy'])->name('services.destroy');

    // Financials
    Route::get('/financials', [\App\Http\Controllers\Admin\AdminFinancialController::class, 'index'])->name('financials.index');
});


// Freelancer Routes
Route::get('/freelancer/register', [App\Http\Controllers\Auth\FreelancerRegisterController::class, 'showRegistrationForm'])->name('freelancer.register.form');
Route::post('/freelancer/register', [App\Http\Controllers\Auth\FreelancerRegisterController::class, 'register'])->name('freelancer.register');

Route::middleware(['auth', 'freelancer'])->prefix('freelancer')->name('freelancer.')->group(function () {
    Route::get('/onboarding/skills', [App\Http\Controllers\Freelancer\OnboardingController::class, 'showSkills'])->name('onboarding.skills');
    Route::post('/onboarding/skills', [App\Http\Controllers\Freelancer\OnboardingController::class, 'storeSkills'])->name('onboarding.skills.store');
    
    // Reuse Expert Dashboard
    Route::get('/dashboard', [ExpertDashboardController::class, 'index'])->name('dashboard');
});

