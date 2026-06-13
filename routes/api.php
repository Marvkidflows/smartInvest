<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Auth
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\LoginController;

// Shared
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
| PUBLIC AUTH
|--------------------------------------------------------------------------
*/
Route::post('/register/stage1', [RegisterController::class, 'submitStage1']);
Route::post('/login',           [LoginController::class, 'login']);

/*
|--------------------------------------------------------------------------
| AUTHENTICATED (Bearer token required)
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {

    Route::get('/user', function (Request $request) {
        $user = $request->user();
        return response()->json([
            'id'            => $user->id,
            'name'          => $user->name ?? $user->full_name,
            'email'         => $user->email,
            'role'          => $user->role,
            'balance'       => (float) ($user->balance ?? 0),
            'referral_code' => $user->referral_code ?? null,
            'status'        => $user->status ?? 'active',
        ]);
    });

    Route::post('/logout', [LoginController::class, 'logout']);

    // Registration continuation
    Route::post('/register/stage2', [RegisterController::class, 'submitStage2']);
    Route::post('/register/stage3', [RegisterController::class, 'submitStage3']);
    Route::post('/register/stage4', [RegisterController::class, 'submitStage4']);

    /*
    |----------------------------------------------------------------------
    | INVESTOR ROUTES
    |----------------------------------------------------------------------
    */
    Route::middleware('investor')
        ->prefix('investor-investment')
        ->name('investor-investment.')
        ->group(function () {

        Route::get('/dashboard', [InvestorDashboardController::class, 'dashboard'])
            ->name('dashboard');

        Route::get('/notifications',                      [NotificationController::class, 'index'])
            ->name('notifications.index');
        Route::post('/notifications/{notification}/read', [NotificationController::class, 'markAsRead'])
            ->name('notifications.read');
        Route::delete('/notifications/{notification}',    [NotificationController::class, 'destroy'])
            ->name('notifications.destroy');

        Route::get('/announcements', [InvestorAnnouncementController::class, 'investorIndex'])
            ->name('announcements.index');

        Route::get('/messages',           [MessageController::class, 'investorIndex'])->name('messages.index');
        Route::get('/messages/create',    [MessageController::class, 'investorCreate'])->name('messages.create');
        Route::post('/messages',          [MessageController::class, 'investorStore'])->name('messages.store');
        Route::get('/messages/{message}', [MessageController::class, 'investorShow'])->name('messages.show');

        Route::get('/investor/profile',      [InvestorProfileController::class, 'show'])->name('profile.show');
        Route::get('/investor/profile/edit', [InvestorProfileController::class, 'edit'])->name('profile.edit');
        Route::put('/investor/profile',      [InvestorProfileController::class, 'update'])->name('profile.update');

        Route::get('/investor/investments',                     [InvestorInvestmentController::class, 'index'])->name('investments.index');
        Route::get('/investor/investments/plans',               [InvestorInvestmentController::class, 'plans'])->name('investments.plans');
        Route::get('/investor/investments/create/{plan}',       [InvestorInvestmentController::class, 'create'])->name('investments.create');
        Route::post('/investor/investments',                    [InvestorInvestmentController::class, 'store'])->name('investments.store');
        Route::get('/investor/investments/{investmentAccount}', [InvestorInvestmentController::class, 'show'])->name('investments.show');

        Route::get('/investor/deposits',           [InvestorDepositController::class, 'index'])->name('deposits.index');
        Route::get('/investor/deposits/create',    [InvestorDepositController::class, 'create'])->name('deposits.create');
        Route::post('/investor/deposits',          [InvestorDepositController::class, 'store'])->name('deposits.store');
        Route::get('/investor/deposits/{deposit}', [InvestorDepositController::class, 'show'])->name('deposits.show');

        Route::get('/investor/withdrawals',        [InvestorWithdrawalController::class, 'index'])->name('withdrawals.index');
        Route::get('/investor/withdrawals/create', [InvestorWithdrawalController::class, 'create'])->name('withdrawals.create');
        Route::post('/investor/withdrawals',       [InvestorWithdrawalController::class, 'store'])->name('withdrawals.store');

        Route::get('/investor/referrals', [InvestorReferralController::class, 'index'])->name('referrals.index');
    });

    /*
    |----------------------------------------------------------------------
    | ADMIN ROUTES
    |----------------------------------------------------------------------
    */
    Route::middleware('admin')
        ->prefix('admin')
        ->name('admin.')
        ->group(function () {

        Route::get('/dashboard', [AdminDashboardController::class, 'dashboard'])
            ->name('dashboard');

        Route::get('/analytics', [AdminAnalyticsController::class, 'index'])
            ->name('analytics');

        Route::get('/users',                  [AdminUserController::class, 'index'])->name('users.index');
        Route::get('/users/{user}',           [AdminUserController::class, 'show'])->name('users.show');
        Route::put('/users/{user}',           [AdminUserController::class, 'update'])->name('users.update');
        Route::post('/users/{user}/suspend',  [AdminUserController::class, 'suspend'])->name('users.suspend');
        Route::post('/users/{user}/activate', [AdminUserController::class, 'activate'])->name('users.activate');

        Route::resource('investment-plans', AdminInvestmentPlanController::class);

        Route::get('/investments',                        [AdminInvestmentController::class, 'index'])->name('investments.index');
        Route::get('/investments/{investment}',           [AdminInvestmentController::class, 'show'])->name('investments.show');
        Route::post('/investments/{investment}/complete', [AdminInvestmentController::class, 'complete'])->name('investments.complete');

        Route::get('/deposits',                    [AdminDepositController::class, 'index'])->name('deposits.index');
        Route::get('/deposits/{deposit}',          [AdminDepositController::class, 'show'])->name('deposits.show');
        Route::post('/deposits/{deposit}/approve', [AdminDepositController::class, 'approve'])->name('deposits.approve');
        Route::post('/deposits/{deposit}/reject',  [AdminDepositController::class, 'reject'])->name('deposits.reject');

        Route::get('/withdrawals',                       [AdminWithdrawalController::class, 'index'])->name('withdrawals.index');
        Route::post('/withdrawals/{withdrawal}/approve', [AdminWithdrawalController::class, 'approve'])->name('withdrawals.approve');
        Route::post('/withdrawals/{withdrawal}/reject',  [AdminWithdrawalController::class, 'reject'])->name('withdrawals.reject');

        Route::get('/messages',                  [MessageController::class, 'adminIndex'])->name('messages.index');
        Route::post('/messages/{investor}/send', [MessageController::class, 'adminSend'])->name('messages.send');
        Route::delete('/messages/{message}',     [MessageController::class, 'adminDelete'])->name('messages.destroy');
        Route::get('/messages/{investor}',       [MessageController::class, 'adminShow'])->name('messages.show');

        Route::resource('announcements', AdminAnnouncementController::class);
    });

    Route::get('/notifications/mark-all-read', function (Request $request) {
        $user = $request->user();
        $user->unreadNotifications->markAsRead();
        return response()->json(['message' => 'All notifications marked as read.']);
    })->name('notifications.mark-all-read');
});