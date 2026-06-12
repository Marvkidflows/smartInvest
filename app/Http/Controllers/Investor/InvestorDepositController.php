<?php
// LOCATION: app/Http/Controllers/Investor/InvestorDepositController.php

namespace App\Http\Controllers\Investor;

use App\Http\Controllers\Controller;
use App\Models\Deposit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InvestorDepositController extends Controller
{
    // GET /investor-investment/investor/deposits
    public function index(Request $request)
    {
        $user     = Auth::user();
        $deposits = Deposit::where('user_id', $user->id)
            ->latest()
            ->get()
            ->map(fn($d) => [
                'id'         => $d->id,
                'amount'     => (float) $d->amount,
                'method'     => $d->payment_method ?? $d->method ?? 'N/A',
                'reference'  => $d->transaction_reference ?? $d->reference ?? null,
                'status'     => $d->status,
                'created_at' => $d->created_at->toDateString(),
            ]);

        if ($request->expectsJson()) {
            return response()->json(['deposits' => $deposits]);
        }
        return view('investor.deposits.index', compact('deposits'));
    }

    // GET /investor-investment/investor/deposits/create
    public function create(Request $request)
    {
        // Return payment methods / wallet addresses for the investor
        $paymentInfo = [
            'bitcoin'  => config('payment.bitcoin_address',  'bc1qexampleaddress'),
            'ethereum' => config('payment.ethereum_address', '0xExampleAddress'),
            'usdt'     => config('payment.usdt_address',     'TExampleAddress'),
        ];

        if ($request->expectsJson()) {
            return response()->json(['payment_info' => $paymentInfo]);
        }
        return view('investor.deposits.create', compact('paymentInfo'));
    }

    // POST /investor-investment/investor/deposits
    public function store(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'amount'         => ['required', 'numeric', 'min:50'],
            'payment_method' => ['required', 'string', 'in:bitcoin,ethereum,usdt,bank_transfer'],
            'reference'      => ['required', 'string', 'max:255'],
        ]);

        $deposit = Deposit::create([
            'user_id'               => $user->id,
            'amount'                => $validated['amount'],
            'payment_method'        => $validated['payment_method'],
            'method'                => $validated['payment_method'],
            'transaction_reference' => $validated['reference'],
            'reference'             => $validated['reference'],
            'status'                => 'pending',
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Deposit submitted! Awaiting admin approval.',
                'deposit' => [
                    'id'         => $deposit->id,
                    'amount'     => (float) $deposit->amount,
                    'method'     => $deposit->payment_method,
                    'reference'  => $deposit->transaction_reference,
                    'status'     => $deposit->status,
                    'created_at' => $deposit->created_at->toDateString(),
                ],
            ], 201);
        }

        return redirect()->route('investor-investment.deposits.index')
            ->with('success', 'Deposit submitted! Awaiting admin approval.');
    }

    // GET /investor-investment/investor/deposits/{deposit}
    public function show(Request $request, Deposit $deposit)
    {
        // Ensure investor owns this deposit
        if ($deposit->user_id !== Auth::id()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthorized.'], 403);
            }
            abort(403);
        }

        $data = [
            'deposit' => [
                'id'         => $deposit->id,
                'amount'     => (float) $deposit->amount,
                'method'     => $deposit->payment_method ?? $deposit->method ?? 'N/A',
                'reference'  => $deposit->transaction_reference ?? $deposit->reference ?? null,
                'status'     => $deposit->status,
                'notes'      => $deposit->notes ?? null,
                'created_at' => $deposit->created_at->toDateString(),
                'approved_at'=> optional($deposit->approved_at)->toDateString(),
            ],
        ];

        if ($request->expectsJson()) {
            return response()->json($data);
        }
        return view('investor.deposits.show', $data);
    }
}
