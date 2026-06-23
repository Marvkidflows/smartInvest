<?php
// LOCATION: app/Http/Controllers/Investor/InvestorInvestmentController.php

namespace App\Http\Controllers\Investor;

use App\Http\Controllers\Controller;
use App\Models\InvestmentAccount;
use App\Models\InvestmentPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class InvestorInvestmentController extends Controller
{
    // GET /investor-investment/investor/investments
    public function index(Request $request)
    {
        $user        = Auth::user();
        $investments = InvestmentAccount::where('user_id', $user->id)
            ->with('investmentPlan')
            ->latest()
            ->get()
            ->map(function ($inv) {
                $totalDays = $inv->investmentPlan->duration_days
                    ?? (($inv->investmentPlan->duration_months ?? 1) * 30);
                $remaining = $inv->remaining_days ?? $totalDays;
                $elapsed   = $totalDays - $remaining;
                $progress  = $totalDays > 0 ? round(($elapsed / $totalDays) * 100) : 0;

                return [
                    'id'             => $inv->id,
                    'plan_name'      => $inv->investmentPlan->name ?? 'N/A',
                    'amount'         => (float) $inv->amount,
                    'profit_percent' => (float) ($inv->investmentPlan->profit_percentage
                                        ?? $inv->investmentPlan->profit_percent ?? 0),
                    'expected_profit'=> (float) ($inv->expected_profit ?? 0),
                    'start_date'     => optional($inv->start_date)->toDateString(),
                    'end_date'       => optional($inv->end_date)->toDateString(),
                    'days_remaining' => $remaining,
                    'total_days'     => $totalDays,
                    'days_passed'    => $elapsed,
                    'progress'       => min(100, max(0, $progress)),
                    'status'         => $inv->status,
                    'created_at'     => $inv->created_at->toDateString(),
                    'plan'           => [
                        'id'   => $inv->investmentPlan->id ?? null,
                        'name' => $inv->investmentPlan->name ?? 'N/A',
                    ],
                ];
            });

        if ($request->expectsJson()) {
            return response()->json(['investments' => $investments]);
        }
        return view('investor.investments.index', compact('investments'));
    }

    // GET /investor-investment/investor/investments/plans
    public function plans(Request $request)
    {
        $plans = InvestmentPlan::where('status', 'active')
            ->with('sectorCategory.sector')
            ->orderBy('min_amount')
            ->get()
            ->map(fn($p) => [
                'id'                   => $p->id,
                'name'                 => $p->name,
                'description'          => $p->description ?? null,
                'sector_category_id'   => $p->sector_category_id,
                'sector_category_name' => $p->sectorCategory->name ?? null,
                'sector_id'            => $p->sectorCategory->sector->id ?? null,
                'sector_name'          => $p->sectorCategory->sector->name ?? null,
                'sector_icon'          => $p->sectorCategory->sector->icon ?? null,
                'min_amount'           => (float) $p->min_amount,
                'max_amount'           => $p->max_amount ? (float) $p->max_amount : null,
                'profit_percent'       => (float) ($p->profit_percentage ?? $p->profit_percent ?? 0),
                'roi_percent'          => (float) ($p->profit_percentage ?? $p->profit_percent ?? 0),
                'duration_days'        => $p->duration_days ?? (($p->duration_months ?? 1) * 30),
                'duration_months'      => $p->duration_months ?? null,
                'is_featured'          => (bool) ($p->is_featured ?? false),
                'status'               => $p->status ?? 'active',
            ]);

        if ($request->expectsJson()) {
            return response()->json(['plans' => $plans]);
        }
        return view('investor.investments.plans', compact('plans'));
    }

    // GET /investor-investment/investor/investments/create/{plan}
    public function create(Request $request, InvestmentPlan $plan)
    {
        if ($request->expectsJson()) {
            return response()->json(['plan' => $plan]);
        }
        return view('investor.investments.create', compact('plan'));
    }

    // POST /investor-investment/investor/investments
    public function store(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'plan_id' => ['required', 'exists:investment_plans,id'],
            'amount'  => ['required', 'numeric', 'min:1'],
        ]);

        $plan = InvestmentPlan::findOrFail($validated['plan_id']);

        // Validate amount against plan limits
        if ($validated['amount'] < $plan->min_amount) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => "Minimum investment for this plan is $" . number_format($plan->min_amount, 2),
                ], 422);
            }
            return back()->withErrors(['amount' => 'Amount is below the minimum for this plan.']);
        }

        if ($plan->max_amount && $validated['amount'] > $plan->max_amount) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => "Maximum investment for this plan is $" . number_format($plan->max_amount, 2),
                ], 422);
            }
            return back()->withErrors(['amount' => 'Amount exceeds the maximum for this plan.']);
        }

        // Check user balance
        if ($user->balance < $validated['amount']) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Insufficient balance. Please make a deposit first.',
                ], 422);
            }
            return back()->withErrors(['amount' => 'Insufficient balance.']);
        }

        $durationDays   = $plan->duration_days ?? (($plan->duration_months ?? 1) * 30);
        $profitPercent  = $plan->profit_percentage ?? $plan->profit_percent ?? 0;
        $expectedProfit = $validated['amount'] * ($profitPercent / 100);
        $totalReturn    = $validated['amount'] + $expectedProfit;

        // Create investment
        $investment = InvestmentAccount::create([
            'user_id'            => $user->id,
            'investment_plan_id' => $plan->id,
            'amount'             => $validated['amount'],
            'profit_percentage'  => $profitPercent,
            'expected_profit'    => $expectedProfit,
            'total_return'       => $totalReturn,
            'status'             => 'active',
            'start_date'         => now(),
            'end_date'           => now()->addDays($durationDays),
            'remaining_days'     => $durationDays,
        ]);

        // Deduct from user balance
        $user->decrement('balance', $validated['amount']);

        if ($request->expectsJson()) {
            return response()->json([
                'message'    => 'Investment created successfully!',
                'investment' => [
                    'id'             => $investment->id,
                    'plan_name'      => $plan->name,
                    'amount'         => (float) $investment->amount,
                    'profit_percent' => $profitPercent,
                    'expected_profit'=> (float) $expectedProfit,
                    'start_date'     => $investment->start_date->toDateString(),
                    'end_date'       => $investment->end_date->toDateString(),
                    'status'         => $investment->status,
                ],
            ], 201);
        }

        return redirect()->route('investor-investment.investments.index')
            ->with('success', 'Investment created!');
    }

    // GET /investor-investment/investor/investments/{investmentAccount}
    public function show(Request $request, InvestmentAccount $investmentAccount)
    {
        // Ensure investor owns this investment
        if ($investmentAccount->user_id !== Auth::id()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthorized.'], 403);
            }
            abort(403);
        }

        $investmentAccount->load('investmentPlan');
        $totalDays = $investmentAccount->investmentPlan->duration_days
            ?? (($investmentAccount->investmentPlan->duration_months ?? 1) * 30);
        $remaining = $investmentAccount->remaining_days ?? $totalDays;

        $data = [
            'investment' => [
                'id'             => $investmentAccount->id,
                'plan_name'      => $investmentAccount->investmentPlan->name ?? 'N/A',
                'amount'         => (float) $investmentAccount->amount,
                'profit_percent' => (float) ($investmentAccount->investmentPlan->profit_percentage ?? 0),
                'expected_profit'=> (float) ($investmentAccount->expected_profit ?? 0),
                'start_date'     => optional($investmentAccount->start_date)->toDateString(),
                'end_date'       => optional($investmentAccount->end_date)->toDateString(),
                'days_remaining' => $remaining,
                'total_days'     => $totalDays,
                'progress'       => $totalDays > 0
                    ? round((($totalDays - $remaining) / $totalDays) * 100)
                    : 0,
                'status'         => $investmentAccount->status,
                'plan'           => $investmentAccount->investmentPlan,
            ],
        ];

        if ($request->expectsJson()) {
            return response()->json($data);
        }
        return view('investor.investments.show', $data);
    }
}