<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    protected $fillable = [
        'title',
        'description',
        'reward',
        'active_date',
        'is_active',
    ];

    protected $casts = [
        'reward' => 'decimal:2',
        'active_date' => 'date',
        'is_active' => 'boolean',
    ];

    public function completions()
    {
        return $this->belongsToMany(User::class, 'task_completions')
                    ->withTimestamps()
                    ->withPivot('completed_at');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)
                    ->where('active_date', '<=', now());
    }
}