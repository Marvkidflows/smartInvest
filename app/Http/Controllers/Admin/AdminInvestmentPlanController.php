<?php
// LOCATION: app/Http/Controllers/Admin/AdminInvestmentPlanController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InvestmentPlan;
use Illuminate\Http\Request;

class AdminInvestmentPlanController extends Controller
{
    // GET /admin/investment-plans
    public function index(Request $request)
    {
        $plans = InvestmentPlan::withCount('investmentAccounts')->latest()->get()
            ->map(fn($p) => [
                'id'                      => $p->id,
                'name'                    => $p->name,
                'description'             => $p->description ?? null,
                'min_amount'              => (float) $p->min_amount,
                'max_amount'              => $p->max_amount ? (float) $p->max_amount : null,
                'profit_percent'          => (float) ($p->profit_percentage ?? $p->profit_percent ?? 0),
                'duration_days'           => $p->duration_days ?? ($p->duration_months * 30 ?? 30),
                'duration_months'         => $p->duration_months ?? null,
                'is_featured'             => (bool) ($p->is_featured ?? false),
                'status'                  => $p->status ?? 'active',
                'investment_accounts_count'=> $p->investment_accounts_count,
                'created_at'              => $p->created_at->toDateString(),
            ]);

        if ($request->expectsJson()) {
            return response()->json(['plans' => $plans]);
        }
        return view('admin.plans.index', compact('plans'));
    }

    // GET /admin/investment-plans/create
    public function create()
    {
        return view('admin.plans.create');
    }

    // POST /admin/investment-plans
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'            => ['required', 'string', 'max:255'],
            'description'     => ['nullable', 'string'],
            'min_amount'      => ['required', 'numeric', 'min:1'],
            'max_amount'      => ['nullable', 'numeric', 'gt:min_amount'],
            'profit_percent'  => ['required', 'numeric', 'min:0', 'max:100'],
            'duration_days'   => ['required', 'integer', 'min:1'],
            'is_featured'     => ['nullable', 'boolean'],
            'status'          => ['nullable', 'in:active,inactive'],
        ]);

        $plan = InvestmentPlan::create([
            'name'               => $validated['name'],
            'description'        => $validated['description'] ?? null,
            'min_amount'         => $validated['min_amount'],
            'max_amount'         => $validated['max_amount'] ?? null,
            'profit_percentage'  => $validated['profit_percent'],
            'profit_percent'     => $validated['profit_percent'],
            'duration_days'      => $validated['duration_days'],
            'duration_months'    => ceil($validated['duration_days'] / 30),
            'is_featured'        => $request->boolean('is_featured'),
            'status'             => $validated['status'] ?? 'active',
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Investment plan created.',
                'plan'    => $plan,
            ], 201);
        }
        return redirect()->route('admin.investment-plans.index')->with('success', 'Plan created.');
    }

    // GET /admin/investment-plans/{plan}
    public function show(Request $request, InvestmentPlan $investmentPlan)
    {
        if ($request->expectsJson()) {
            return response()->json(['plan' => $investmentPlan]);
        }
        return view('admin.plans.show', compact('investmentPlan'));
    }

    // GET /admin/investment-plans/{plan}/edit
    public function edit(InvestmentPlan $investmentPlan)
    {
        return view('admin.plans.edit', compact('investmentPlan'));
    }

    // PUT /admin/investment-plans/{plan}
    public function update(Request $request, InvestmentPlan $investmentPlan)
    {
        $validated = $request->validate([
            'name'           => ['sometimes', 'string', 'max:255'],
            'description'    => ['nullable', 'string'],
            'min_amount'     => ['sometimes', 'numeric', 'min:1'],
            'max_amount'     => ['nullable', 'numeric'],
            'profit_percent' => ['sometimes', 'numeric', 'min:0', 'max:100'],
            'duration_days'  => ['sometimes', 'integer', 'min:1'],
            'is_featured'    => ['nullable', 'boolean'],
            'status'         => ['nullable', 'in:active,inactive'],
        ]);

        $investmentPlan->update([
            'name'              => $validated['name']           ?? $investmentPlan->name,
            'description'       => $validated['description']    ?? $investmentPlan->description,
            'min_amount'        => $validated['min_amount']     ?? $investmentPlan->min_amount,
            'max_amount'        => $validated['max_amount']     ?? $investmentPlan->max_amount,
            'profit_percentage' => $validated['profit_percent'] ?? $investmentPlan->profit_percentage,
            'profit_percent'    => $validated['profit_percent'] ?? $investmentPlan->profit_percent,
            'duration_days'     => $validated['duration_days']  ?? $investmentPlan->duration_days,
            'is_featured'       => $request->boolean('is_featured'),
            'status'            => $validated['status']         ?? $investmentPlan->status,
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Plan updated.',
                'plan'    => $investmentPlan->fresh(),
            ]);
        }
        return redirect()->route('admin.investment-plans.index')->with('success', 'Plan updated.');
    }

    // DELETE /admin/investment-plans/{plan}
    public function destroy(Request $request, InvestmentPlan $investmentPlan)
    {
        // Don't delete if plans have active investments
        if ($investmentPlan->investmentAccounts()->where('status', 'active')->exists()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Cannot delete a plan with active investments.'], 422);
            }
            return back()->withErrors(['error' => 'Cannot delete a plan with active investments.']);
        }

        $investmentPlan->delete();

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Plan deleted.']);
        }
        return redirect()->route('admin.investment-plans.index')->with('success', 'Plan deleted.');
    }
}
