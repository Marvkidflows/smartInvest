<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Auth
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Auth\EmailVerificationController;

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
use App\Http\Controllers\Admin\AdminEmailVerificationController;
use App\Http\Controllers\Admin\AdminSectorController;
use App\Http\Controllers\Admin\AdminGlobalManagementController;
use App\Http\Controllers\Admin\AdminKycController;
use App\Http\Controllers\Admin\AdminEmailController;
use App\Http\Controllers\Investor\InvestorEmailController;
 
// Investor
use App\Http\Controllers\Investor\InvestorDashboardController;
use App\Http\Controllers\Investor\InvestorInvestmentController;
use App\Http\Controllers\Investor\InvestorDepositController;
use App\Http\Controllers\Investor\InvestorWithdrawalController;
use App\Http\Controllers\Investor\InvestorReferralController;
use App\Http\Controllers\Investor\InvestorProfileController;
use App\Http\Controllers\Investor\InvestorAnnouncementController;
use App\Http\Controllers\Investor\WithdrawalPinController;

/*
|--------------------------------------------------------------------------
| PUBLIC AUTH
|--------------------------------------------------------------------------
*/
Route::post('/register/stage1', [RegisterController::class, 'submitStage1']);
Route::post('/login',           [LoginController::class, 'login']);
Route::post('/forgot-password', [PasswordResetController::class, 'forgotPassword']);
Route::post('/reset-password', [PasswordResetController::class, 'resetPassword']);
 Route::post('/public/contact-support', [MessageController::class, 'publicContactSupport']);

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
    // Email OTP verification (between stage 1 and stage 2)
    Route::post('/register/verify-otp', [EmailVerificationController::class, 'verify']);
    Route::post('/register/resend-otp', [EmailVerificationController::class, 'resend']);
    Route::post('/register/stage2', [RegisterController::class, 'submitStage2']);
    Route::post('/register/stage3', [RegisterController::class, 'submitStage3']);
    Route::post('/register/stage4', [RegisterController::class, 'submitStage4']);

    // Sectors — readable by any authenticated user (investor or admin)
    Route::get('/sectors/active', function () {
        return response()->json([
            'sectors' => \App\Models\Sector::with('activeCategories')->active()->ordered()->get(),
        ]);
    });

    /*
    |----------------------------------------------------------------------
    | INVESTOR ROUTES
    |----------------------------------------------------------------------
    */
    Route::middleware(['investor', 'check.account'])
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
        
        
Route::get('/emails',               [InvestorEmailController::class, 'index'])->name('emails.index');
Route::get('/emails/{sentEmail}',   [InvestorEmailController::class, 'show'])->name('emails.show');

        Route::get('/investor/profile',      [InvestorProfileController::class, 'show'])->name('profile.show');
        Route::get('/investor/profile/edit', [InvestorProfileController::class, 'edit'])->name('profile.edit');
        Route::put('/investor/profile',      [InvestorProfileController::class, 'update'])->name('profile.update');
        
Route::post('/investor/profile/kyc', [InvestorProfileController::class, 'submitKyc'])->name('profile.kyc');
 
      // ── Investments ──
Route::get('/investor/investments',                     [InvestorInvestmentController::class, 'index'])->name('investments.index');
Route::get('/investor/investments/plans',               [InvestorInvestmentController::class, 'plans'])->name('investments.plans');
Route::get('/investor/investments/create/{plan}',       [InvestorInvestmentController::class, 'create'])->name('investments.create');
Route::post('/investor/investments',                    [InvestorInvestmentController::class, 'store'])->name('investments.store');
Route::get('/investor/investments/{investmentAccount}', [InvestorInvestmentController::class, 'show'])->name('investments.show');

// ── Deposits ──
Route::get('/investor/deposits',                    [InvestorDepositController::class, 'index'])->name('deposits.index');
Route::get('/investor/deposits/create',             [InvestorDepositController::class, 'create'])->name('deposits.create');
Route::post('/investor/deposits/initiate',          [InvestorDepositController::class, 'initiate'])->name('deposits.initiate');
Route::put('/investor/deposits/{deposit}/confirm',  [InvestorDepositController::class, 'confirm'])->name('deposits.confirm');
Route::post('/investor/deposits',                   [InvestorDepositController::class, 'store'])->name('deposits.store'); // backward-compat alias → initiate
Route::get('/investor/deposits/{deposit}',          [InvestorDepositController::class, 'show'])->name('deposits.show');

        Route::get('/investor/withdrawals',        [InvestorWithdrawalController::class, 'index'])->name('withdrawals.index');
        Route::get('/investor/withdrawals/create', [InvestorWithdrawalController::class, 'create'])->name('withdrawals.create');
        Route::post('/investor/withdrawals',       [InvestorWithdrawalController::class, 'store'])->name('withdrawals.store');


        Route::get('/investor/withdrawal-pin/status', [WithdrawalPinController::class, 'status'])->name('withdrawal-pin.status');
        Route::post('/investor/withdrawal-pin',        [WithdrawalPinController::class, 'store'])->name('withdrawal-pin.store');
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
        Route::post('/users/{user}/balance',  [AdminUserController::class, 'adjustBalance'])->name('users.balance');     
Route::post('/users/{user}/freeze',     [AdminUserController::class, 'freeze'])->name('users.freeze');
Route::post('/users/{user}/unfreeze',   [AdminUserController::class, 'unfreeze'])->name('users.unfreeze');
Route::post('/users/{user}/deactivate', [AdminUserController::class, 'deactivate'])->name('users.deactivate');
 

        // Email OTP verification — admin controls
        Route::get('/users/{user}/verification-status', [AdminEmailVerificationController::class, 'status'])->name('users.verification-status');
        Route::post('/users/{user}/resend-otp',          [AdminEmailVerificationController::class, 'resend'])->name('users.resend-otp');
        Route::post('/users/{user}/manual-verify',       [AdminEmailVerificationController::class, 'manualVerify'])->name('users.manual-verify');

     Route::post('/global/profit-adjustments', [AdminGlobalManagementController::class, 'adjustProfit'])->name('global.profit-adjust');
Route::get('/global/profit-adjustments',  [AdminGlobalManagementController::class, 'profitAdjustmentHistory'])->name('global.profit-history');
Route::post('/global/balance-bulk',       [AdminGlobalManagementController::class, 'bulkBalance'])->name('global.balance-bulk');
 

        Route::resource('investment-plans', AdminInvestmentPlanController::class);

        // Sectors & categories
        Route::get('/sectors',                      [AdminSectorController::class, 'index'])->name('sectors.index');
        Route::post('/sectors',                      [AdminSectorController::class, 'store'])->name('sectors.store');
        Route::put('/sectors/{sector}',               [AdminSectorController::class, 'update'])->name('sectors.update');
        Route::delete('/sectors/{sector}',            [AdminSectorController::class, 'destroy'])->name('sectors.destroy');
        Route::post('/sectors/{sector}/activate',     [AdminSectorController::class, 'activate'])->name('sectors.activate');
        Route::post('/sectors/{sector}/deactivate',   [AdminSectorController::class, 'deactivate'])->name('sectors.deactivate');

        Route::post('/sectors/{sector}/categories',              [AdminSectorController::class, 'storeCategory'])->name('sectors.categories.store');
        Route::put('/sector-categories/{category}',               [AdminSectorController::class, 'updateCategory'])->name('sector-categories.update');
        Route::delete('/sector-categories/{category}',            [AdminSectorController::class, 'destroyCategory'])->name('sector-categories.destroy');
        Route::post('/sector-categories/{category}/activate',     [AdminSectorController::class, 'activateCategory'])->name('sector-categories.activate');
        Route::post('/sector-categories/{category}/deactivate',   [AdminSectorController::class, 'deactivateCategory'])->name('sector-categories.deactivate');

        Route::get('/investments',                        [AdminInvestmentController::class, 'index'])->name('investments.index');
        Route::get('/investments/{investment}',           [AdminInvestmentController::class, 'show'])->name('investments.show');
        Route::post('/investments/{investment}/complete', [AdminInvestmentController::class, 'complete'])->name('investments.complete');

        Route::post('/investments/{investment}/countdown/extend',  [AdminInvestmentController::class, 'extendCountdown'])->name('investments.countdown.extend');
Route::post('/investments/{investment}/countdown/reduce',  [AdminInvestmentController::class, 'reduceCountdown'])->name('investments.countdown.reduce');
Route::post('/investments/{investment}/countdown/set-date',[AdminInvestmentController::class, 'setCountdownDate'])->name('investments.countdown.set-date');
Route::post('/investments/{investment}/countdown/override',[AdminInvestmentController::class, 'overrideCountdown'])->name('investments.countdown.override');
Route::get('/investments/{investment}/countdown/logs',     [AdminInvestmentController::class, 'countdownLogs'])->name('investments.countdown.logs');

        Route::get('/deposits',                    [AdminDepositController::class, 'index'])->name('deposits.index');
        Route::get('/deposits/{deposit}',          [AdminDepositController::class, 'show'])->name('deposits.show');
        Route::post('/deposits/{deposit}/approve', [AdminDepositController::class, 'approve'])->name('deposits.approve');
        Route::post('/deposits/{deposit}/reject',  [AdminDepositController::class, 'reject'])->name('deposits.reject');
        Route::post('/deposits/{deposit}/hold',  [AdminDepositController::class, 'hold'])->name('deposits.hold');
        Route::post('/deposits/{deposit}/notes', [AdminDepositController::class, 'addNote'])->name('deposits.notes');

        
Route::get('/kyc',                    [AdminKycController::class, 'index'])->name('kyc.index');
Route::get('/kyc/{user}',             [AdminKycController::class, 'show'])->name('kyc.show');
Route::post('/kyc/{user}/approve',    [AdminKycController::class, 'approve'])->name('kyc.approve');
Route::post('/kyc/{user}/reject',     [AdminKycController::class, 'reject'])->name('kyc.reject');
 

        Route::get('/withdrawals',                       [AdminWithdrawalController::class, 'index'])->name('withdrawals.index');
        Route::get('/withdrawals/{withdrawal}',          [AdminWithdrawalController::class, 'show'])->name('withdrawals.show');
        Route::post('/withdrawals/{withdrawal}/approve', [AdminWithdrawalController::class, 'approve'])->name('withdrawals.approve');
        Route::post('/withdrawals/{withdrawal}/reject',  [AdminWithdrawalController::class, 'reject'])->name('withdrawals.reject');
        Route::post('/withdrawals/{withdrawal}/hold',  [AdminWithdrawalController::class, 'hold'])->name('withdrawals.hold');
        Route::post('/withdrawals/{withdrawal}/notes', [AdminWithdrawalController::class, 'addNote'])->name('withdrawals.notes');


        Route::get('/messages',                  [MessageController::class, 'adminIndex'])->name('messages.index');
        Route::post('/messages/{investor}/send', [MessageController::class, 'adminSend'])->name('messages.send');
        Route::delete('/messages/{message}',     [MessageController::class, 'adminDelete'])->name('messages.destroy');
        Route::get('/messages/{investor}',       [MessageController::class, 'adminShow'])->name('messages.show');

    /*
|--------------------------------------------------------------------------
| EMAIL CENTER
|--------------------------------------------------------------------------
*/

Route::prefix('email-center')->name('email-center.')->group(function () {

    // Dashboard
    Route::get('/dashboard', [AdminEmailController::class, 'dashboard'])
        ->name('dashboard');
    Route::get('/countries', [AdminEmailController::class, 'countries'])->name('countries');
    // Search Investors
    Route::get('/investors/search', [AdminEmailController::class, 'searchInvestors'])
        ->name('investors.search');

    // Send Email
    Route::post('/send', [AdminEmailController::class, 'send'])
        ->name('send');

    Route::post('/send-test', [AdminEmailController::class, 'sendTest'])
        ->name('send-test');

    // Bulk Email
    Route::get('/bulk/count', [AdminEmailController::class, 'bulkCount'])
        ->name('bulk.count');

    Route::post('/bulk/send', [AdminEmailController::class, 'bulkSend'])
        ->name('bulk.send');

    // Templates
    Route::get('/templates', [AdminEmailController::class, 'templatesIndex'])
        ->name('templates.index');

    Route::post('/templates', [AdminEmailController::class, 'templatesStore'])
        ->name('templates.store');

    Route::put('/templates/{template}', [AdminEmailController::class, 'templatesUpdate'])
        ->name('templates.update');

    Route::delete('/templates/{template}', [AdminEmailController::class, 'templatesDestroy'])
        ->name('templates.destroy');

    // Email Logs
    Route::get('/logs', [AdminEmailController::class, 'logs'])
        ->name('logs');

    Route::get('/logs/{sentEmail}', [AdminEmailController::class, 'logsShow'])
        ->name('logs.show');

});
        
        Route::resource('announcements', AdminAnnouncementController::class);
    });

    Route::get('/notifications/mark-all-read', function (Request $request) {
        $user = $request->user();
        $user->unreadNotifications->markAsRead();
        return response()->json(['message' => 'All notifications marked as read.']);
    })->name('notifications.mark-all-read');
});