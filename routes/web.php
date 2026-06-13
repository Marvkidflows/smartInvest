<?php
// LOCATION: routes/web.php
// COPY AND PASTE THIS ENTIRE FILE — REPLACE YOUR EXISTING web.php

use Illuminate\Support\Facades\Route;

// Auth
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\LoginController;

// Shared
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\NotificationController;

// Admin
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminInvestmentPlanController;
use App\Http\Controllers\Admin\AdminDepositController;
use App\Http\Controllers\Admin\AdminWithdrawalController;
use App\Http\Controllers\Admin\AdminAnnouncementController;
use App\Http\Controllers\Admin\AdminAnalyticsController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\AdminInvestmentController;

// Investor
use App\Http\Controllers\Investor\InvestorDashboardController;
use App\Http\Controllers\Investor\InvestorInvestmentController;
use App\Http\Controllers\Investor\InvestorDepositController;
use App\Http\Controllers\Investor\InvestorWithdrawalController;
use App\Http\Controllers\Investor\InvestorReferralController;
use App\Http\Controllers\Investor\InvestorProfileController;
use App\Http\Controllers\Investor\InvestorAnnouncementController;

/*
|--------------------------------------------------------------------------
| PUBLIC PAGES
|--------------------------------------------------------------------------
*/
Route::get('/',             [HomeController::class, 'index'])->name('home');
Route::get('/about',        [HomeController::class, 'about'])->name('about');
Route::get('/plans',        [HomeController::class, 'plans'])->name('plans');
Route::get('/how-it-works', [HomeController::class, 'howItWorks'])->name('how-it-works');
Route::get('/faq',          [HomeController::class, 'faq'])->name('faq');
Route::get('/contact',      [HomeController::class, 'contact'])->name('contact');
Route::post('/contact',     [HomeController::class, 'contactSubmit'])->name('contact.submit');

/*
|--------------------------------------------------------------------------
| AUTHENTICATION
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {

    Route::get('/login',  [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.submit');

    Route::get('/debug-session-config', function () {
    return response()->json([
        'env_same_site'    => env('SESSION_SAME_SITE'),
        'config_same_site' => config('session.same_site'),
        'env_secure'       => env('SESSION_SECURE_COOKIE'),
        'config_secure'    => config('session.secure'),
        'config_driver'    => config('session.driver'),
        'app_env'          => config('app.env'),
    ]);
});
    Route::get('/register',         [RegisterController::class, 'showStage1'])->name('register');
    Route::post('/register/stage1', [RegisterController::class, 'submitStage1'])->name('register.stage1.submit');

    Route::get('/register/stage2',  [RegisterController::class, 'showStage2'])->name('register.stage2');
    Route::post('/register/stage2', [RegisterController::class, 'submitStage2'])->name('register.stage2.submit');

    Route::get('/register/stage3',  [RegisterController::class, 'showStage3'])->name('register.stage3');
    Route::post('/register/stage3', [RegisterController::class, 'submitStage3'])->name('register.stage3.submit');

    Route::get('/register/stage4',  [RegisterController::class, 'showStage4'])->name('register.stage4');
    Route::post('/register/stage4', [RegisterController::class, 'submitStage4'])->name('register.stage4.submit');
});

/*
|--------------------------------------------------------------------------
| LOGOUT
|--------------------------------------------------------------------------
*/
Route::post('/logout', [LoginController::class, 'logout'])
    ->name('logout')
    ->middleware('auth');

/*
|--------------------------------------------------------------------------
| INVESTOR ROUTES
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'investor'])
    ->prefix('investor-investment')
    ->name('investor-investment.')
    ->group(function () {

    // Dashboard
    Route::get('/dashboard', [InvestorDashboardController::class, 'dashboard'])
        ->name('dashboard');

    // Notifications
    Route::get('/notifications',                      [NotificationController::class, 'index'])
        ->name('notifications.index');
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markAsRead'])
        ->name('notifications.read');
    Route::delete('/notifications/{notification}',    [NotificationController::class, 'destroy'])
        ->name('notifications.destroy');

    // Announcements — shows in investor notification bell
    Route::get('/announcements', [InvestorAnnouncementController::class, 'investorIndex'])
        ->name('announcements.index');

    // Messages — investor sends to admin, sees replies
    // RULE: /messages/create MUST come before /messages/{message}
    Route::get('/messages',           [MessageController::class, 'investorIndex'])->name('messages.index');
    Route::get('/messages/create',    [MessageController::class, 'investorCreate'])->name('messages.create');
    Route::post('/messages',          [MessageController::class, 'investorStore'])->name('messages.store');
    Route::get('/messages/{message}', [MessageController::class, 'investorShow'])->name('messages.show');

    // Profile
    Route::get('/investor/profile',      [InvestorProfileController::class, 'show'])->name('profile.show');
    Route::get('/investor/profile/edit', [InvestorProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/investor/profile',      [InvestorProfileController::class, 'update'])->name('profile.update');

    // Investments
    // RULE: /plans and /create/{plan} MUST come before /{investmentAccount}
    Route::get('/investor/investments',                     [InvestorInvestmentController::class, 'index'])->name('investments.index');
    Route::get('/investor/investments/plans',               [InvestorInvestmentController::class, 'plans'])->name('investments.plans');
    Route::get('/investor/investments/create/{plan}',       [InvestorInvestmentController::class, 'create'])->name('investments.create');
    Route::post('/investor/investments',                    [InvestorInvestmentController::class, 'store'])->name('investments.store');
    Route::get('/investor/investments/{investmentAccount}', [InvestorInvestmentController::class, 'show'])->name('investments.show');

    // Deposits
    // RULE: /create MUST come before /{deposit}
    Route::get('/investor/deposits',           [InvestorDepositController::class, 'index'])->name('deposits.index');
    Route::get('/investor/deposits/create',    [InvestorDepositController::class, 'create'])->name('deposits.create');
    Route::post('/investor/deposits',          [InvestorDepositController::class, 'store'])->name('deposits.store');
    Route::get('/investor/deposits/{deposit}', [InvestorDepositController::class, 'show'])->name('deposits.show');

    // Withdrawals
    // RULE: /create MUST come before any wildcard
    Route::get('/investor/withdrawals',        [InvestorWithdrawalController::class, 'index'])->name('withdrawals.index');
    Route::get('/investor/withdrawals/create', [InvestorWithdrawalController::class, 'create'])->name('withdrawals.create');
    Route::post('/investor/withdrawals',       [InvestorWithdrawalController::class, 'store'])->name('withdrawals.store');

    // Referrals
    Route::get('/investor/referrals', [InvestorReferralController::class, 'index'])->name('referrals.index');
});

/*
|--------------------------------------------------------------------------
| ADMIN ROUTES
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

    // Dashboard
    Route::get('/dashboard', [AdminDashboardController::class, 'dashboard'])
        ->name('dashboard');

    // Analytics
    Route::get('/analytics', [AdminAnalyticsController::class, 'index'])
        ->name('analytics');

    // Users
    Route::get('/users',                  [AdminUserController::class, 'index'])->name('users.index');
    Route::get('/users/{user}',           [AdminUserController::class, 'show'])->name('users.show');
    Route::put('/users/{user}',           [AdminUserController::class, 'update'])->name('users.update');
    Route::post('/users/{user}/suspend',  [AdminUserController::class, 'suspend'])->name('users.suspend');
    Route::post('/users/{user}/activate', [AdminUserController::class, 'activate'])->name('users.activate');

    // Investment Plans
    Route::resource('investment-plans', AdminInvestmentPlanController::class);

    // Investments
    Route::get('/investments',                        [AdminInvestmentController::class, 'index'])->name('investments.index');
    Route::get('/investments/{investment}',           [AdminInvestmentController::class, 'show'])->name('investments.show');
    Route::post('/investments/{investment}/complete', [AdminInvestmentController::class, 'complete'])->name('investments.complete');

    // Deposits
    Route::get('/deposits',                    [AdminDepositController::class, 'index'])->name('deposits.index');
    Route::get('/deposits/{deposit}',          [AdminDepositController::class, 'show'])->name('deposits.show');
    Route::post('/deposits/{deposit}/approve', [AdminDepositController::class, 'approve'])->name('deposits.approve');
    Route::post('/deposits/{deposit}/reject',  [AdminDepositController::class, 'reject'])->name('deposits.reject');

    // Withdrawals
    Route::get('/withdrawals',                       [AdminWithdrawalController::class, 'index'])->name('withdrawals.index');
    Route::post('/withdrawals/{withdrawal}/approve', [AdminWithdrawalController::class, 'approve'])->name('withdrawals.approve');
    Route::post('/withdrawals/{withdrawal}/reject',  [AdminWithdrawalController::class, 'reject'])->name('withdrawals.reject');

    // Messages — admin can message ANY investor privately
    // RULE: /send and DELETE must come BEFORE /{investor} catch-all
    Route::get('/messages',                  [MessageController::class, 'adminIndex'])->name('messages.index');
    Route::post('/messages/{investor}/send', [MessageController::class, 'adminSend'])->name('messages.send');
    Route::delete('/messages/{message}',     [MessageController::class, 'adminDelete'])->name('messages.destroy');
    Route::get('/messages/{investor}',       [MessageController::class, 'adminShow'])->name('messages.show');

    // Announcements — admin posts, ALL investors see in notification bell
    Route::resource('announcements', AdminAnnouncementController::class);
});

/*
|--------------------------------------------------------------------------
| MARK ALL NOTIFICATIONS READ
|--------------------------------------------------------------------------
*/
Route::get('/notifications/mark-all-read', function () {
    $user = auth()->user();
    if ($user) {
        $user->unreadNotifications->markAsRead();
    }
    if (request()->expectsJson()) {
        return response()->json(['message' => 'All notifications marked as read.']);
    }
    return back();
})->name('notifications.mark-all-read')->middleware('auth');
