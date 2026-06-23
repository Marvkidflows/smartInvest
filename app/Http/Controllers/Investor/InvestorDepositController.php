<?php
// LOCATION: app/Http/Controllers/Investor/InvestorDepositController.php

namespace App\Http\Controllers\Investor;

use App\Http\Controllers\Controller;
use App\Models\Deposit;
use App\Models\InvestmentPlan;
use App\Services\TelegramService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class InvestorDepositController extends Controller
{
    protected TelegramService $telegram;

    public function __construct(TelegramService $telegram)
    {
        $this->telegram = $telegram;
    }

    // GET /investor-investment/investor/deposits
    public function index(Request $request)
    {
        $user     = Auth::user();
        $deposits = Deposit::where('user_id', $user->id)
            ->with('investmentPlan:id,name')
            ->latest()
            ->get()
            ->map(fn($d) => [
                'id'         => $d->id,
                'amount'     => (float) $d->amount,
                'method'     => $d->payment_method,
                'reference'  => $d->transaction_reference,
                'plan_name'  => $d->investmentPlan->name ?? null,
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
        $plans = InvestmentPlan::where('status', 'active')->get(['id', 'name', 'min_amount', 'max_amount', 'profit_percentage']);

        $agent = [
            'name'     => config('services.telegram.agent_name', 'Agent Frank'),
            'username' => config('services.telegram.agent_username', 'AgentFrank'),
            'link'     => 'https://t.me/' . ltrim(config('services.telegram.agent_username', 'Agentlanfrank'), '@'),
        ];

        if ($request->expectsJson()) {
            return response()->json(['plans' => $plans, 'agent' => $agent]);
        }
        return view('investor.deposits.create', compact('plans', 'agent'));
    }

    // POST /investor-investment/investor/deposits
    public function store(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'amount'             => ['required', 'numeric', 'min:50'],
            'payment_method'     => ['required', 'string', 'in:bitcoin,ethereum,usdt,bank_transfer'],
            'investment_plan_id' => ['required', 'exists:investment_plans,id'],
            'screenshot'         => ['nullable', 'image', 'max:5120'], // 5MB max
        ]);

        $plan = InvestmentPlan::find($validated['investment_plan_id']);
        if ($plan) {
            if ($validated['amount'] < $plan->min_amount || ($plan->max_amount && $validated['amount'] > $plan->max_amount)) {
                return response()->json([
                    'message' => "Amount must be between \${$plan->min_amount} and \${$plan->max_amount} for this plan.",
                ], 422);
            }
        }

        $reference = 'DEP-' . now()->year . '-' . strtoupper(Str::random(8));

        $proofImagePath = null;
        if ($request->hasFile('screenshot')) {
            $proofImagePath = $request->file('screenshot')->store('deposit-screenshots', 'public');
        }

        $deposit = Deposit::create([
            'user_id'               => $user->id,
            'investment_plan_id'    => $validated['investment_plan_id'],
            'amount'                => $validated['amount'],
            'payment_method'        => $validated['payment_method'],
            'transaction_reference' => $reference,
            'proof_image'           => $proofImagePath,
            'status'                => 'pending',
        ]);

        $this->telegram->newDeposit(
            $user->name ?? $user->full_name ?? 'Investor',
            (float) $deposit->amount,
            $reference
        );

        $agent = [
            'name'     => config('services.telegram.agent_name', 'Agent Frank'),
            'username' => config('services.telegram.agent_username', 'Agentlanfrank'),
            'link'     => 'https://t.me/' . ltrim(config('services.telegram.agent_username', 'Agentlanfrank'), '@'),
        ];

        $responseData = [
            'id'         => $deposit->id,
            'amount'     => (float) $deposit->amount,
            'method'     => $deposit->payment_method,
            'reference'  => $deposit->transaction_reference,
            'plan_name'  => $plan->name ?? null,
            'status'     => $deposit->status,
            'created_at' => $deposit->created_at->toDateString(),
        ];

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Deposit submitted! Contact the agent below to confirm your payment.',
                'deposit' => $responseData,
                'agent'   => $agent,
            ], 201);
        }

        return redirect()->route('investor-investment.deposits.index')
            ->with('success', 'Deposit submitted! Awaiting admin approval.');
    }

    // GET /investor-investment/investor/deposits/{deposit}
    public function show(Request $request, Deposit $deposit)
    {
        if ($deposit->user_id !== Auth::id()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthorized.'], 403);
            }
            abort(403);
        }

        $deposit->load('investmentPlan:id,name');

        $agent = [
            'name'     => config('services.telegram.agent_name', 'Agent Frank'),
            'username' => config('services.telegram.agent_username', 'Agentlanfrank'),
            'link'     => 'https://t.me/' . ltrim(config('services.telegram.agent_username', 'Agentlanfrank'), '@'),
        ];

        $data = [
            'deposit' => [
                'id'           => $deposit->id,
                'amount'       => (float) $deposit->amount,
                'method'       => $deposit->payment_method,
                'reference'    => $deposit->transaction_reference,
                'plan_name'    => $deposit->investmentPlan->name ?? null,
                'status'       => $deposit->status,
                'admin_notes'  => $deposit->admin_notes,
                'created_at'   => $deposit->created_at->toDateString(),
                'processed_at' => optional($deposit->processed_at)->toDateString(),
            ],
            'agent' => $agent,
        ];

        if ($request->expectsJson()) {
            return response()->json($data);
        }
        return view('investor.deposits.show', $data);
    }
}