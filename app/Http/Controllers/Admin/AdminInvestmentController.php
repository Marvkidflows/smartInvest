<?php
// LOCATION: app/Http/Controllers/Admin/AdminInvestmentController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InvestmentAccount;
use Illuminate\Http\Request;

class AdminInvestmentController extends Controller
{
    // GET /admin/investments
    public function index(Request $request)
    {
        $investments = InvestmentAccount::with(['user:id,name,email', 'investmentPlan'])
            ->latest()
            ->get()
            ->map(fn($i) => [
                'id'             => $i->id,
                'amount'         => (float) $i->amount,
                'profit_percent' => $i->investmentPlan->profit_percentage ?? 0,
                'expected_profit'=> (float) ($i->expected_profit ?? 0),
                'status'         => $i->status,
                'start_date'     => optional($i->start_date)->toDateString(),
                'end_date'       => optional($i->end_date)->toDateString(),
                'days_remaining' => $i->remaining_days ?? 0,
                'created_at'     => $i->created_at->toDateString(),
                'plan'           => [
                    'id'   => $i->investmentPlan->id ?? null,
                    'name' => $i->investmentPlan->name ?? 'N/A',
                ],
                'user' => [
                    'id'    => $i->user->id ?? null,
                    'name'  => $i->user->name ?? 'Unknown',
                    'email' => $i->user->email ?? '',
                ],
            ]);

        if ($request->expectsJson()) {
            return response()->json(['investments' => $investments]);
        }
        return view('admin.investments.index', compact('investments'));
    }

    // GET /admin/investments/{investment}
    public function show(Request $request, InvestmentAccount $investment)
    {
        $investment->load(['user', 'investmentPlan']);
        $data = [
            'id'              => $investment->id,
            'amount'          => (float) $investment->amount,
            'profit_percent'  => $investment->investmentPlan->profit_percentage ?? 0,
            'expected_profit' => (float) ($investment->expected_profit ?? 0),
            'status'          => $investment->status,
            'start_date'      => optional($investment->start_date)->toDateString(),
            'end_date'        => optional($investment->end_date)->toDateString(),
            'days_remaining'  => $investment->remaining_days ?? 0,
            'plan'            => $investment->investmentPlan,
            'user'            => $investment->user,
        ];

        if ($request->expectsJson()) {
            return response()->json(['investment' => $data]);
        }
        return view('admin.investments.show', compact('investment'));
    }

    // POST /admin/investments/{investment}/complete
    public function complete(Request $request, InvestmentAccount $investment)
    {
        $investment->update([
            'status'       => 'completed',
            'completed_at' => now(),
        ]);

        // Credit profit to user's balance
        $user = $investment->user;
        if ($user) {
            $user->increment('balance', $investment->expected_profit ?? 0);
        }

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Investment marked as completed. Profit credited.']);
        }
        return back()->with('success', 'Investment completed.');
    }
}
