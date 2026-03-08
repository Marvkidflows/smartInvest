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

Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {

    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');

    Route::get('/users', [AdminController::class, 'users'])->name('admin.users');
    Route::post('/users/{user}/suspend', [AdminController::class, 'suspendUser'])->name('admin.users.suspend');
    Route::post('/users/{user}/activate', [AdminController::class, 'activateUser'])->name('admin.users.activate');

    Route::get('/tasks', [AdminController::class, 'tasks'])->name('admin.tasks');
    Route::post('/tasks', [AdminController::class, 'createTask'])->name('admin.tasks.create');
    Route::delete('/tasks/{task}', [AdminController::class, 'deleteTask'])->name('admin.tasks.delete');

    Route::get('/withdrawals', [AdminController::class, 'withdrawals'])->name('admin.withdrawals');
    Route::post('/withdrawals/{transaction}/approve', [AdminController::class, 'approveWithdrawal'])->name('admin.withdrawals.approve');
    Route::post('/withdrawals/{transaction}/decline', [AdminController::class, 'declineWithdrawal'])->name('admin.withdrawals.decline');

    Route::get('/notifications', [AdminController::class, 'notifications'])->name('admin.notifications');
    Route::post('/notifications', [AdminController::class, 'sendNotification'])->name('admin.notifications.send');
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