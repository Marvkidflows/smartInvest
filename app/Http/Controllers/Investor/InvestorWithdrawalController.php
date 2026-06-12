<?php
// LOCATION: app/Http/Controllers/Investor/InvestorWithdrawalController.php

namespace App\Http\Controllers\Investor;

use App\Http\Controllers\Controller;
use App\Models\Withdrawal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InvestorWithdrawalController extends Controller
{
    // GET /investor-investment/investor/withdrawals
    public function index(Request $request)
    {
        $user        = Auth::user();
        $withdrawals = Withdrawal::where('user_id', $user->id)
            ->latest()
            ->get()
            ->map(fn($w) => [
                'id'             => $w->id,
                'amount'         => (float) $w->amount,
                'method'         => $w->method ?? $w->withdrawal_method ?? 'N/A',
                'wallet_address' => $w->wallet_address ?? null,
                'bank_name'      => $w->bank_name ?? null,
                'account_number' => $w->account_number ?? null,
                'status'         => $w->status,
                'created_at'     => $w->created_at->toDateString(),
                'approved_at'    => optional($w->approved_at)->toDateString(),
            ]);

        if ($request->expectsJson()) {
            return response()->json(['withdrawals' => $withdrawals]);
        }
        return view('investor.withdrawals.index', compact('withdrawals'));
    }

    // GET /investor-investment/investor/withdrawals/create
    public function create(Request $request)
    {
        $user = Auth::user();

        if ($request->expectsJson()) {
            return response()->json([
                'available_balance' => (float) ($user->balance ?? 0),
            ]);
        }
        return view('investor.withdrawals.create', compact('user'));
    }

    // POST /investor-investment/investor/withdrawals
    public function store(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'amount'         => ['required', 'numeric', 'min:10'],
            'method'         => ['required', 'string', 'in:bitcoin,ethereum,usdt,bank_transfer'],
            'wallet_address' => ['required_if:method,bitcoin,ethereum,usdt', 'nullable', 'string', 'max:255'],
            'bank_name'      => ['required_if:method,bank_transfer', 'nullable', 'string', 'max:100'],
            'account_number' => ['required_if:method,bank_transfer', 'nullable', 'string', 'max:50'],
            'account_name'   => ['required_if:method,bank_transfer', 'nullable', 'string', 'max:100'],
        ]);

        // Check balance
        if ($user->balance < $validated['amount']) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Insufficient balance. Your available balance is $' . number_format($user->balance, 2),
                ], 422);
            }
            return back()->withErrors(['amount' => 'Insufficient balance.']);
        }

        // Check for pending withdrawal already
        $hasPending = Withdrawal::where('user_id', $user->id)
            ->where('status', 'pending')
            ->exists();

        if ($hasPending) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'You already have a pending withdrawal request. Please wait for it to be processed.',
                ], 422);
            }
            return back()->withErrors(['amount' => 'You already have a pending withdrawal.']);
        }

        $withdrawal = Withdrawal::create([
            'user_id'        => $user->id,
            'amount'         => $validated['amount'],
            'method'         => $validated['method'],
            'withdrawal_method' => $validated['method'],
            'wallet_address' => $validated['wallet_address'] ?? null,
            'bank_name'      => $validated['bank_name'] ?? null,
            'account_number' => $validated['account_number'] ?? null,
            'account_name'   => $validated['account_name'] ?? null,
            'status'         => 'pending',
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'message'    => 'Withdrawal request submitted! Awaiting admin approval.',
                'withdrawal' => [
                    'id'         => $withdrawal->id,
                    'amount'     => (float) $withdrawal->amount,
                    'method'     => $withdrawal->method,
                    'status'     => $withdrawal->status,
                    'created_at' => $withdrawal->created_at->toDateString(),
                ],
            ], 201);
        }

        return redirect()->route('investor-investment.withdrawals.index')
            ->with('success', 'Withdrawal request submitted!');
    }
}
