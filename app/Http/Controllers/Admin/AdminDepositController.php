<?php
// LOCATION: app/Http/Controllers/Admin/AdminDepositController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Deposit;
use App\Models\User;
use Illuminate\Http\Request;

class AdminDepositController extends Controller
{
    // GET /admin/deposits
    public function index(Request $request)
    {
        $query = Deposit::with('user:id,name,email')->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $deposits = $query->get()->map(fn($d) => [
            'id'         => $d->id,
            'amount'     => (float) $d->amount,
            'method'     => $d->payment_method ?? $d->method ?? 'N/A',
            'reference'  => $d->transaction_reference ?? $d->reference ?? null,
            'status'     => $d->status,
            'created_at' => $d->created_at->toDateString(),
            'user' => [
                'id'    => $d->user->id ?? null,
                'name'  => $d->user->name ?? 'Unknown',
                'email' => $d->user->email ?? '',
            ],
        ]);

        if ($request->expectsJson()) {
            return response()->json(['deposits' => $deposits]);
        }
        return view('admin.deposits.index', compact('deposits'));
    }

    // GET /admin/deposits/{deposit}
    public function show(Request $request, Deposit $deposit)
    {
        $deposit->load('user');
        $data = [
            'id'         => $deposit->id,
            'amount'     => (float) $deposit->amount,
            'method'     => $deposit->payment_method ?? $deposit->method ?? 'N/A',
            'reference'  => $deposit->transaction_reference ?? $deposit->reference ?? null,
            'status'     => $deposit->status,
            'notes'      => $deposit->notes ?? null,
            'created_at' => $deposit->created_at->toDateString(),
            'user' => [
                'id'    => $deposit->user->id ?? null,
                'name'  => $deposit->user->name ?? 'Unknown',
                'email' => $deposit->user->email ?? '',
            ],
        ];

        if ($request->expectsJson()) {
            return response()->json(['deposit' => $data]);
        }
        return view('admin.deposits.show', compact('deposit'));
    }

    // POST /admin/deposits/{deposit}/approve
    public function approve(Request $request, Deposit $deposit)
    {
        if ($deposit->status !== 'pending') {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Deposit is not pending.'], 422);
            }
            return back()->withErrors(['error' => 'Deposit is not pending.']);
        }

        $deposit->update(['status' => 'approved', 'approved_at' => now()]);

        // Credit the user's balance
        $user = User::find($deposit->user_id);
        if ($user) {
            $user->increment('balance', $deposit->amount);
        }

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Deposit approved. Balance credited.', 'status' => 'approved']);
        }
        return back()->with('success', 'Deposit approved.');
    }

    // POST /admin/deposits/{deposit}/reject
    public function reject(Request $request, Deposit $deposit)
    {
        if ($deposit->status !== 'pending') {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Deposit is not pending.'], 422);
            }
            return back()->withErrors(['error' => 'Deposit is not pending.']);
        }

        $deposit->update([
            'status'      => 'rejected',
            'rejected_at' => now(),
            'reject_reason' => $request->reason ?? null,
        ]);

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Deposit rejected.', 'status' => 'rejected']);
        }
        return back()->with('success', 'Deposit rejected.');
    }
}
