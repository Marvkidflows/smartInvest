<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Task;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Notifications\AdminNotification;

class AdminController extends Controller
{
    public function dashboard()
    {
        $stats = [
            'total_users' => User::where('role', 'investor')->count(),
            'pending_withdrawals' => Transaction::where('type', 'withdrawal')
                ->where('status', 'pending')
                ->sum('amount'),
            'total_deposits' => Transaction::where('type', 'deposit')
                ->where('status', 'completed')
                ->sum('amount'),
            'active_tasks' => Task::active()->count(),
        ];

        return view('admin.dashboard', compact('stats'));
    }

    public function users()
    {
        $users = User::where('role', 'investor')->latest()->paginate(20);
        return view('admin.users', compact('users'));
    }

    public function suspendUser(User $user)
    {
        $user->update(['status' => 'suspended']);
        return back()->with('success', 'User suspended successfully');
    }

    public function activateUser(User $user)
    {
        $user->update(['status' => 'active']);
        return back()->with('success', 'User activated successfully');
    }

    public function tasks()
    {
        $tasks = Task::latest()->paginate(20);
        return view('admin.tasks', compact('tasks'));
    }

    public function createTask(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'reward' => 'required|numeric|min:0',
            'active_date' => 'required|date',
        ]);

        Task::create($validated);

        return back()->with('success', 'Task created successfully');
    }

    public function deleteTask(Task $task)
    {
        $task->delete();
        return back()->with('success', 'Task deleted successfully');
    }

    public function withdrawals()
    {
        $withdrawals = Transaction::where('type', 'withdrawal')
            ->with('user')
            ->latest()
            ->paginate(20);

        return view('admin.withdrawals', compact('withdrawals'));
    }

    public function approveWithdrawal(Transaction $transaction)
    {
        $transaction->update(['status' => 'completed']);
        
        // Deduct from user balance
        $transaction->user->balance -= $transaction->amount;
        $transaction->user->save();

        // Notify user
        $transaction->user->notify(new AdminNotification(
            'Withdrawal Approved',
            "Your withdrawal of $$transaction->amount has been approved and processed."
        ));

        return back()->with('success', 'Withdrawal approved');
    }

    public function declineWithdrawal(Transaction $transaction)
    {
        $transaction->update(['status' => 'declined']);

        // Notify user
        $transaction->user->notify(new AdminNotification(
            'Withdrawal Declined',
            "Your withdrawal request of $$transaction->amount has been declined."
        ));

        return back()->with('success', 'Withdrawal declined');
    }

    public function notifications()
    {
        return view('admin.notifications');
    }

    public function sendNotification(Request $request)
    {
        $validated = $request->validate([
            'recipient' => 'required|in:all,tier_specific',
            'tier' => 'nullable|required_if:recipient,tier_specific',
            'title' => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        if ($validated['recipient'] === 'all') {
            $users = User::where('role', 'investor')->get();
        } else {
            $users = User::where('role', 'investor')
                ->where('tier', $validated['tier'])
                ->get();
        }

        foreach ($users as $user) {
            $user->notify(new AdminNotification(
                $validated['title'],
                $validated['message']
            ));
        }

        return back()->with('success', 'Notification sent successfully');
    }
}