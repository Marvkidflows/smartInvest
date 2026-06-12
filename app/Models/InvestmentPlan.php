<?php
// LOCATION: app/Models/InvestmentPlan.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvestmentPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'min_amount',
        'max_amount',
        'profit_percentage',
        'profit_percent',
        'duration_days',
        'duration_months',
        'is_featured',
        'status',
    ];

    protected $casts = [
        'min_amount'        => 'decimal:2',
        'max_amount'        => 'decimal:2',
        'profit_percentage' => 'decimal:2',
        'profit_percent'    => 'decimal:2',
        'is_featured'       => 'boolean',
    ];

    // ── RELATIONSHIPS ──────────────────────────────────────────────────────

    public function investmentAccounts()
    {
        return $this->hasMany(InvestmentAccount::class);
    }

    // Active investments only
    public function activeInvestments()
    {
        return $this->hasMany(InvestmentAccount::class)->where('status', 'active');
    }

    // ── HELPERS ────────────────────────────────────────────────────────────

    // Get the ROI % (handles both column names)
    public function getRoiAttribute(): float
    {
        return (float) ($this->profit_percentage ?? $this->profit_percent ?? 0);
    }

    // Get duration in days (handles both column names)
    public function getDaysAttribute(): int
    {
        return $this->duration_days ?? (($this->duration_months ?? 1) * 30);
    }

    // Scope: only active plans
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    // Scope: featured plans first
    public function scopeFeaturedFirst($query)
    {
        return $query->orderByDesc('is_featured')->orderBy('min_amount');
    }
}
