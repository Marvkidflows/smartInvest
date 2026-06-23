<?php
// LOCATION: app/Models/InvestmentAccount.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvestmentAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'investment_plan_id',
        'amount',
        'profit_percentage',
        'expected_profit',
        'total_return',
        'start_date',
        'end_date',
        'remaining_days',
        'status',
    ];

    protected $casts = [
        'amount'            => 'decimal:2',
        'profit_percentage' => 'decimal:2',
        'expected_profit'   => 'decimal:2',
        'total_return'      => 'decimal:2',
        'start_date'        => 'date',
        'end_date'          => 'date',
    ];

    // ── RELATIONSHIPS ──────────────────────────────────────────────────────

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function investmentPlan()
    {
        return $this->belongsTo(InvestmentPlan::class);
    }

    // ── HELPERS ────────────────────────────────────────────────────────────

    // Calculate progress percentage
    public function getProgressAttribute(): int
    {
        $plan      = $this->investmentPlan;
        $totalDays = $plan
            ? ($plan->duration_days ?? (($plan->duration_months ?? 1) * 30))
            : 30;
        $remaining = $this->remaining_days ?? $totalDays;
        $elapsed   = $totalDays - $remaining;

        return (int) min(100, max(0, round(($elapsed / $totalDays) * 100)));
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }
}