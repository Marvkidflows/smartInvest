<?php
// LOCATION: app/Http/Controllers/Admin/AdminDashboardController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\InvestmentPlan;
use App\Models\InvestmentAccount;
use App\Models\Message;
use App\Models\Deposit;
use App\Models\Withdrawal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminDashboardController extends Controller
{
    public function dashboard(Request $request)
    {
        // ── STATS ─────────────────────────────────────────────────────────────
        $totalInvestors      = User::where('role', 'investor')->count();
        $activeInvestors     = User::where('role', 'investor')->where('status', 'active')->count();
        $totalInvested       = InvestmentAccount::sum('amount') ?? 0;
        $totalProfit         = InvestmentAccount::sum('expected_profit') ?? 0;
        $totalDeposits       = Deposit::where('status', 'approved')->sum('amount') ?? 0;
        $totalWithdrawals    = Withdrawal::where('status', 'approved')->sum('amount') ?? 0;
        $pendingWithdrawals  = Withdrawal::where('status', 'pending')->count();
        $pendingDeposits     = Deposit::where('status', 'pending')->count();
        $companyRevenue      = $totalDeposits - $totalWithdrawals;
        $monthlyProfit       = Deposit::where('status', 'approved')
                                ->whereMonth('created_at', now()->month)
                                ->sum('amount') ?? 0;

        // ── MONTHLY CHART DATA ─────────────────────────────────────────────
        $months = collect(range(1, 12))->map(function ($month) {
            $label = date('M', mktime(0, 0, 0, $month, 1));
            return [
                'month'       => $label,
                'revenue'     => (float) Deposit::where('status', 'approved')
                                    ->whereMonth('created_at', $month)
                                    ->whereYear('created_at', now()->year)
                                    ->sum('amount'),
                'deposits'    => (float) Deposit::whereMonth('created_at', $month)
                                    ->whereYear('created_at', now()->year)
                                    ->sum('amount'),
                'withdrawals' => (float) Withdrawal::whereMonth('created_at', $month)
                                    ->whereYear('created_at', now()->year)
                                    ->sum('amount'),
                'investors'   => User::where('role', 'investor')
                                    ->whereMonth('created_at', $month)
                                    ->whereYear('created_at', now()->year)
                                    ->count(),
            ];
        });

        // ── PENDING WITHDRAWALS ────────────────────────────────────────────
        $pendingWithdrawalList = Withdrawal::where('status', 'pending')
            ->with('user:id,name,email')
            ->latest()
            ->take(10)
            ->get()
            ->map(fn($w) => [
                'id'     => $w->id,
                'amount' => (float) $w->amount,
                'method' => $w->method ?? $w->withdrawal_method ?? 'N/A',
                'status' => $w->status,
                'date'   => $w->created_at->toDateString(),
                'user'   => [
                    'id'    => $w->user->id ?? null,
                    'name'  => $w->user->name ?? 'Unknown',
                    'email' => $w->user->email ?? '',
                ],
            ]);

        // ── RECENT INVESTORS ───────────────────────────────────────────────
        $recentInvestors = User::where('role', 'investor')
            ->latest()
            ->take(8)
            ->get()
            ->map(fn($u) => [
                'id'            => $u->id,
                'name'          => $u->name ?? $u->full_name,
                'email'         => $u->email,
                'balance'       => (float) ($u->balance ?? 0),
                'status'        => $u->status ?? 'active',
                'last_login_at' => $u->last_login_at ?? null,
                'created_at'    => $u->created_at->toDateString(),
            ]);

        // ── PLAN DISTRIBUTION ─────────────────────────────────────────────
        $planDistribution = InvestmentPlan::withCount([
                'investmentAccounts as active_count' => fn($q) => $q->where('status', 'active')
            ])
            ->get()
            ->map(fn($p) => [
                'name'  => $p->name,
                'value' => $p->active_count,
            ]);

        // ── RESPONSE ──────────────────────────────────────────────────────
        $data = [
            'stats' => [
                'total_investors'     => $totalInvestors,
                'active_investors'    => $activeInvestors,
                'total_investments'   => (float) $totalInvested,
                'total_deposits'      => (float) $totalDeposits,
                'total_withdrawals'   => (float) $totalWithdrawals,
                'pending_withdrawals' => $pendingWithdrawals,
                'pending_deposits'    => $pendingDeposits,
                'company_revenue'     => (float) $companyRevenue,
                'monthly_profit'      => (float) $monthlyProfit,
                'total_profit'        => (float) $totalProfit,
            ],
            'chart_data'          => $months->values(),
            'plan_distribution'   => $planDistribution->values(),
            'pending_withdrawals' => $pendingWithdrawalList,
            'recent_investors'    => $recentInvestors,
        ];

        if ($request->expectsJson()) {
            return response()->json($data);
        }
        return view('admin.dashboard', $data);
    }
}
