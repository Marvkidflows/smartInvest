<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /*
    |--------------------------------------------------------------------------
    | Mass Assignable Fields
    |--------------------------------------------------------------------------
    */

    protected $fillable = [

        // Basic Info
        'name',
        'full_name',
        'email',
        'password',
        'country_code',
        'phone',
        'country',
        'referral_code',

        // Legal Agreements
        'terms_accepted',
        'risk_accepted',

        // Registration Tracking
        'registration_stage',
        'registration_completed',

        // KYC Information
        'id_type',
        'id_number',
        'id_document_path',
        'selfie_path',
        'date_of_birth',
        'residential_address',
        'city',
        'state',
        'postal_code',
        'kyc_status',

        // Investor Profile
        'employment_status',
        'annual_income_range',
        'source_of_funds',
        'investment_experience',
        'risk_tolerance',
        'investment_objectives',

        // Security
        'withdrawal_pin',
        'two_factor_enabled',
        'two_factor_secret',

        // System Fields
        'role',
        'balance',
        'tier',
        'status',
    ];

    /*
    |--------------------------------------------------------------------------
    | Hidden Fields
    |--------------------------------------------------------------------------
    */

    protected $hidden = [
        'password',
        'remember_token',
        'withdrawal_pin',
        'two_factor_secret',
    ];

    /*
    |--------------------------------------------------------------------------
    | Type Casting
    |--------------------------------------------------------------------------
    */

    protected $casts = [
        'email_verified_at' => 'datetime',
        'balance' => 'decimal:2',
        'terms_accepted' => 'boolean',
        'risk_accepted' => 'boolean',
        'registration_completed' => 'boolean',
        'two_factor_enabled' => 'boolean',
        'date_of_birth' => 'date',
    ];

    /*
    |--------------------------------------------------------------------------
    | Role Helpers
    |--------------------------------------------------------------------------
    */

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isInvestor(): bool
    {
        return $this->role === 'investor';
    }

    /*
    |--------------------------------------------------------------------------
    | Registration Helpers
    |--------------------------------------------------------------------------
    */

    public function hasCompletedRegistration(): bool
    {
        return $this->registration_completed === true;
    }

    public function isKycApproved(): bool
    {
        return $this->kyc_status === 'approved';
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    // User Transactions
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    // User Investments
    public function investments()
    {
        return $this->hasMany(Investment::class);
    }

    // Completed Tasks
    public function completedTasks()
    {
        return $this->belongsToMany(Task::class, 'task_completions')
            ->withPivot('completed_at')
            ->withTimestamps();
    }

    // Task Completion Records
    public function taskCompletions()
    {
        return $this->hasMany(TaskCompletion::class);
    }

    // Notifications
    public function notifications()
    {
        return $this->hasMany(Notification::class)->latest();
    }

    /*
    |--------------------------------------------------------------------------
    | Task Helper
    |--------------------------------------------------------------------------
    */

    public function hasCompletedTask($taskId): bool
    {
        return $this->completedTasks()
            ->where('task_id', $taskId)
            ->exists();
    }
}