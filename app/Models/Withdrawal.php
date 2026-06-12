<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Withdrawal extends Model
{
    protected $fillable = [
        'user_id', 'amount', 'method', 'account_details',
        'status', 'admin_notes', 'processed_at', 'processed_by'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'processed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function approveWithdrawal()
    {
        // Deduct from user balance
        $this->user->decrement('balance', $this->amount);
        $this->user->decrement('locked_balance', $this->amount);

        // Create transaction
        Transaction::create([
            'user_id' => $this->user_id,
            'type' => 'withdrawal',
            'amount' => $this->amount,
            'method' => $this->method,
            'status' => 'completed',
            'description' => "Withdrawal via {$this->method}",
        ]);

        // Update status
        $this->update([
            'status' => 'completed',
            'processed_at' => now(),
            'processed_by' => auth()->id(),
        ]);

        // Notification
        Notification::create([
            'user_id' => $this->user_id,
            'title' => 'Withdrawal Approved!',
            'message' => "Your withdrawal of \${$this->amount} has been approved and will be sent to {$this->method}.",
            'type' => 'success',
        ]);
    }

    public function rejectWithdrawal($reason)
    {
        // Unlock balance
        $this->user->decrement('locked_balance', $this->amount);

        $this->update([
            'status' => 'rejected',
            'admin_notes' => $reason,
            'processed_at' => now(),
            'processed_by' => auth()->id(),
        ]);

        Notification::create([
            'user_id' => $this->user_id,
            'title' => 'Withdrawal Rejected',
            'message' => "Your withdrawal request has been rejected. Reason: {$reason}",
            'type' => 'error',
        ]);
    }
}