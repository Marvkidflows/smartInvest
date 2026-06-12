<?php
// LOCATION: app/Http/Controllers/Admin/AdminUserController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\InvestmentAccount;
use App\Models\Deposit;
use App\Models\Withdrawal;
use Illuminate\Http\Request;

class AdminUserController extends Controller
{
    // GET /admin/users
    public function index(Request $request)
    {
        $query = User::where('role', 'investor');

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(fn($q) => $q->where('name', 'like', "%$s%")
                                      ->orWhere('email', 'like', "%$s%"));
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $users = $query->latest()->get()->map(fn($u) => [
            'id'            => $u->id,
            'name'          => $u->name ?? $u->full_name,
            'email'         => $u->email,
            'phone'         => $u->phone ?? null,
            'country'       => $u->country ?? null,
            'balance'       => (float) ($u->balance ?? 0),
            'status'        => $u->status ?? 'active',
            'role'          => $u->role,
            'last_login_at' => $u->last_login_at ?? null,
            'created_at'    => $u->created_at->toDateString(),
        ]);

        if ($request->expectsJson()) {
            return response()->json(['users' => $users]);
        }
        return view('admin.users.index', compact('users'));
    }

    // GET /admin/users/{user}
    public function show(Request $request, User $user)
    {
        $investments = InvestmentAccount::where('user_id', $user->id)
            ->with('investmentPlan')
            ->latest()
            ->get()
            ->map(fn($i) => [
                'id'             => $i->id,
                'plan_name'      => $i->investmentPlan->name ?? 'N/A',
                'amount'         => (float) $i->amount,
                'profit_percent' => $i->investmentPlan->profit_percentage ?? 0,
                'expected_profit'=> (float) ($i->expected_profit ?? 0),
                'status'         => $i->status,
                'start_date'     => optional($i->start_date)->toDateString(),
                'end_date'       => optional($i->end_date)->toDateString(),
                'days_remaining' => $i->remaining_days ?? 0,
                'created_at'     => $i->created_at->toDateString(),
            ]);

        $deposits = Deposit::where('user_id', $user->id)->latest()->get()
            ->map(fn($d) => [
                'id'         => $d->id,
                'amount'     => (float) $d->amount,
                'method'     => $d->payment_method ?? $d->method ?? 'N/A',
                'status'     => $d->status,
                'created_at' => $d->created_at->toDateString(),
            ]);

        $withdrawals = Withdrawal::where('user_id', $user->id)->latest()->get()
            ->map(fn($w) => [
                'id'         => $w->id,
                'amount'     => (float) $w->amount,
                'method'     => $w->method ?? 'N/A',
                'status'     => $w->status,
                'created_at' => $w->created_at->toDateString(),
            ]);

        $data = [
            'user' => [
                'id'             => $user->id,
                'name'           => $user->name ?? $user->full_name,
                'email'          => $user->email,
                'phone'          => $user->phone ?? null,
                'country'        => $user->country ?? null,
                'address'        => $user->address ?? null,
                'city'           => $user->city ?? null,
                'balance'        => (float) ($user->balance ?? 0),
                'total_invested' => (float) $investments->sum('amount'),
                'total_profit'   => (float) $investments->sum('expected_profit'),
                'status'         => $user->status ?? 'active',
                'role'           => $user->role,
                'created_at'     => $user->created_at->toDateString(),
                'last_login_at'  => $user->last_login_at ?? null,
            ],
            'investments' => $investments,
            'deposits'    => $deposits,
            'withdrawals' => $withdrawals,
        ];

        if ($request->expectsJson()) {
            return response()->json($data);
        }
        return view('admin.users.show', $data);
    }

    // PUT /admin/users/{user}
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name'    => ['sometimes', 'string', 'max:255'],
            'email'   => ['sometimes', 'email', 'unique:users,email,' . $user->id],
            'phone'   => ['sometimes', 'string', 'max:20'],
            'balance' => ['sometimes', 'numeric', 'min:0'],
            'status'  => ['sometimes', 'in:active,suspended,inactive'],
            'country' => ['sometimes', 'string', 'max:100'],
        ]);

        $user->update($validated);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Investor updated successfully.',
                'user'    => $user->fresh(),
            ]);
        }
        return back()->with('success', 'Investor updated.');
    }

    // POST /admin/users/{user}/suspend
    public function suspend(Request $request, User $user)
    {
        $user->update(['status' => 'suspended']);

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Investor suspended.', 'status' => 'suspended']);
        }
        return back()->with('success', 'Investor suspended.');
    }

    // POST /admin/users/{user}/activate
    public function activate(Request $request, User $user)
    {
        $user->update(['status' => 'active']);

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Investor activated.', 'status' => 'active']);
        }
        return back()->with('success', 'Investor activated.');
    }
}
