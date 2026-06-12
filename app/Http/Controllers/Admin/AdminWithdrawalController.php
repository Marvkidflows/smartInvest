<?php
// LOCATION: app/Http/Controllers/Admin/AdminWithdrawalController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Withdrawal;
use App\Models\User;
use Illuminate\Http\Request;

class AdminWithdrawalController extends Controller
{
    // GET /admin/withdrawals
    public function index(Request $request)
    {
        $query = Withdrawal::with('user:id,name,email')->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $withdrawals = $query->get()->map(fn($w) => [
            'id'             => $w->id,
            'amount'         => (float) $w->amount,
            'method'         => $w->method ?? $w->withdrawal_method ?? 'N/A',
            'wallet_address' => $w->wallet_address ?? null,
            'bank_name'      => $w->bank_name ?? null,
            'account_number' => $w->account_number ?? null,
            'account_name'   => $w->account_name ?? null,
            'status'         => $w->status,
            'created_at'     => $w->created_at->toDateString(),
            'user' => [
                'id'    => $w->user->id ?? null,
                'name'  => $w->user->name ?? 'Unknown',
                'email' => $w->user->email ?? '',
            ],
        ]);

        if ($request->expectsJson()) {
            return response()->json(['withdrawals' => $withdrawals]);
        }
        return view('admin.withdrawals.index', compact('withdrawals'));
    }

    // POST /admin/withdrawals/{withdrawal}/approve
    public function approve(Request $request, Withdrawal $withdrawal)
    {
        if ($withdrawal->status !== 'pending') {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Withdrawal is not pending.'], 422);
            }
            return back()->withErrors(['error' => 'Not pending.']);
        }

        // Check user has enough balance
        $user = User::find($withdrawal->user_id);
        if ($user && $user->balance < $withdrawal->amount) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Insufficient user balance.'], 422);
            }
            return back()->withErrors(['error' => 'Insufficient balance.']);
        }

        $withdrawal->update(['status' => 'approved', 'approved_at' => now()]);

        // Deduct from user balance
        if ($user) {
            $user->decrement('balance', $withdrawal->amount);
        }

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Withdrawal approved.', 'status' => 'approved']);
        }
        return back()->with('success', 'Withdrawal approved.');
    }

    // POST /admin/withdrawals/{withdrawal}/reject
    public function reject(Request $request, Withdrawal $withdrawal)
    {
        if ($withdrawal->status !== 'pending') {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Withdrawal is not pending.'], 422);
            }
            return back()->withErrors(['error' => 'Not pending.']);
        }

        $withdrawal->update([
            'status'        => 'rejected',
            'rejected_at'   => now(),
            'reject_reason' => $request->reason ?? null,
        ]);

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Withdrawal rejected.', 'status' => 'rejected']);
        }
        return back()->with('success', 'Withdrawal rejected.');
    }
}
