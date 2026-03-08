<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\InvestorController;
use App\Http\Controllers\AdminController;

/*
|--------------------------------------------------------------------------
| PUBLIC PAGES
|--------------------------------------------------------------------------
*/

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/about', [HomeController::class, 'about'])->name('about');
Route::get('/plans', [HomeController::class, 'plans'])->name('plans');
Route::get('/how-it-works', [HomeController::class, 'howItWorks'])->name('how-it-works');
Route::get('/faq', [HomeController::class, 'faq'])->name('faq');
Route::get('/contact', [HomeController::class, 'contact'])->name('contact');
Route::post('/contact', [HomeController::class, 'contactSubmit'])->name('contact.submit');


/*
|--------------------------------------------------------------------------
| AUTHENTICATION (Multi-Stage Registration)
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {

    // ---------- Registration Stage 1 ----------
    Route::get('/register', [RegisterController::class, 'showStage1'])->name('register');
    Route::post('/register/stage1', [RegisterController::class, 'submitStage1'])->name('register.stage1.submit');

    // ---------- Registration Stage 2 ----------
    Route::get('/register/stage2', [RegisterController::class, 'showStage2'])
        ->name('register.stage2')
        ->middleware('registration.stage:1');

    Route::post('/register/stage2', [RegisterController::class, 'submitStage2'])
        ->name('register.stage2.submit');

    // ---------- Registration Stage 3 ----------
    Route::get('/register/stage3', [RegisterController::class, 'showStage3'])
        ->name('register.stage3')
        ->middleware('registration.stage:2');

    Route::post('/register/stage3', [RegisterController::class, 'submitStage3'])
        ->name('register.stage3.submit');

    // ---------- Registration Stage 4 ----------
    Route::get('/register/stage4', [RegisterController::class, 'showStage4'])
        ->name('register.stage4')
        ->middleware('registration.stage:3');

    Route::post('/register/stage4', [RegisterController::class, 'submitStage4'])
        ->name('register.stage4.submit');


    // ---------- Login ----------
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.submit');
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


Route::middleware(['auth'])->group(function () {
    
    // Dashboard
    Route::get('/dashboard', [InvestorController::class, 'dashboard'])->name('investor.dashboard');
    
    // Investment Plans
   Route::get('/investor/plans', [InvestorController::class, 'plans'])->name('investor.plans');
    Route::post('/investor/investments/create', [InvestorController::class, 'createInvestment'])->name('investor.investments.create');
    Route::get('/investor/investments', [InvestorController::class, 'investments'])->name('investor.investments');
    
    // Tasks
    Route::get('/investor/tasks', [InvestorController::class, 'tasks'])->name('investor.tasks');
    Route::post('/investor/tasks/complete', [InvestorController::class, 'completeTask'])->name('investor.tasks.complete');
    
    // Transactions
    Route::get('/investor/transactions', [InvestorController::class, 'transactions'])->name('investor.transactions');
    Route::get('/investor/transactions/download', [InvestorController::class, 'downloadTransactions'])->name('investor.transactions.download');
    Route::get('/investor/transactions/{id}/receipt', [InvestorController::class, 'downloadReceipt'])->name('investor.transactions.receipt');
    
    // Withdrawals
    Route::get('/investor/withdrawals', [InvestorController::class, 'withdrawals'])->name('investor.withdrawals');
    Route::post('/investor/withdrawals/request', [InvestorController::class, 'requestWithdrawal'])->name('investor.withdrawals.request');
    
    // Notifications
    Route::post('/investor/notifications/{id}/read', [InvestorController::class, 'markNotificationRead'])->name('investor.notifications.read');
    Route::post('/investor/notifications/read-all', [InvestorController::class, 'markAllNotificationsRead'])->name('investor.notifications.read-all');
});

/*
|--------------------------------------------------------------------------
| ADMIN ROUTES
|--------------------------------------------------------------------------
*/
// Admin Routes
Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {

    // ── Dashboard ──────────────────────────────────────────────────
    Route::get('/dashboard',    [AdminController::class, 'dashboard'])->name('admin.dashboard');

    // ── Users ──────────────────────────────────────────────────────
    Route::get('/users',                     [AdminController::class, 'users'])->name('admin.users');
    Route::post('/users/{id}/suspend',       [AdminController::class, 'suspendUser'])->name('admin.users.suspend');
    Route::post('/users/{id}/activate',      [AdminController::class, 'activateUser'])->name('admin.users.activate');
    Route::post('/users/{id}/update',        [AdminController::class, 'updateUser'])->name('admin.users.update');
    Route::post('/users/{id}/update-balance',[AdminController::class, 'updateBalance'])->name('admin.users.update-balance');
    Route::get('/users/{id}/transactions',   [AdminController::class, 'userTransactions'])->name('admin.users.transactions');
    Route::post('/users/message',            [AdminController::class, 'sendMessage'])->name('admin.users.message');
    Route::post('/users/adjust-balance',     [AdminController::class, 'adjustBalance'])->name('admin.users.adjust-balance');

    // ── Investors page ─────────────────────────────────────────────
    Route::get('/investors',    [AdminController::class, 'investors'])->name('admin.investors');

    // ── Tasks ──────────────────────────────────────────────────────
    Route::get('/tasks',                [AdminController::class, 'tasks'])->name('admin.tasks');
    Route::get('/tasks/create',         [AdminController::class, 'createTask'])->name('admin.tasks.create');
    Route::post('/tasks',               [AdminController::class, 'storeTask'])->name('admin.tasks.store');
    Route::put('/tasks/{id}',           [AdminController::class, 'updateTask'])->name('admin.tasks.update');
    Route::patch('/tasks/{id}/toggle',  [AdminController::class, 'toggleTaskStatus'])->name('admin.tasks.toggle');
    Route::delete('/tasks/{id}',        [AdminController::class, 'deleteTask'])->name('admin.tasks.delete');

    // ── Withdrawals ────────────────────────────────────────────────
    Route::get('/withdrawals',                   [AdminController::class, 'withdrawals'])->name('admin.withdrawals');
    Route::post('/withdrawals/{id}/approve',     [AdminController::class, 'approveWithdrawal'])->name('admin.withdrawals.approve');
    Route::post('/withdrawals/{id}/decline',     [AdminController::class, 'declineWithdrawal'])->name('admin.withdrawals.decline');

    // ── Notifications ──────────────────────────────────────────────
    Route::get('/notifications',         [AdminController::class, 'notifications'])->name('admin.notifications');
    Route::get('/notifications/create',  [AdminController::class, 'createNotification'])->name('admin.notifications.create');
    Route::post('/notifications/send',   [AdminController::class, 'sendNotification'])->name('admin.notifications.send');

});
/*
|--------------------------------------------------------------------------
| NOTIFICATIONS
|--------------------------------------------------------------------------
*/

Route::get('/notifications/mark-all-read', function () {
    auth()->user()->unreadNotifications->markAsRead();
    return back();
})->name('notifications.mark-all-read')->middleware('auth');