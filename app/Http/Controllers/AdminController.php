<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Investment;
use App\Models\Transaction;
use App\Models\Task;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class AdminController extends Controller
{
    /**
     * Display admin dashboard
     */
    public function dashboard()
    {
        $data = [
            'totalUsers' => User::where('role', 'investor')->count(),
            'totalInvested' => Investment::sum('amount'),
            'pendingWithdrawals' => Transaction::where('type', 'withdrawal')
                ->where('status', 'pending')
                ->count(),
            'activeTasks' => Task::where('is_active', true)->count(),
            'todaySignups' => User::whereDate('created_at', today())->count(),
            'activeInvestments' => Investment::where('status', 'active')->count(),
            'todayEarnings' => Transaction::where('type', 'profit')
                ->whereDate('created_at', today())
                ->sum('amount'),
            'recentUsers' => User::where('role', 'investor')
                ->latest()
                ->take(10)
                ->get(),
        ];

        return view('admin.dashboard', $data);
    }

    /**
     * Display users management
     */
    public function users()
    {
        $users = User::where('role', 'investor')
            ->withCount('investments')
            ->withSum('investments', 'amount')
            ->latest()
            ->paginate(20);

        foreach ($users as $user) {
            $user->total_invested = $user->investments_sum_amount ?? 0;
            $user->current_plan   = $this->getUserCurrentPlan($user);
            $user->kyc_verified   = $user->kyc_status === 'approved';
        }

        return view('admin.users', compact('users'));
    }

    /**
     * Display investors management page (dynamic slide-over page)
     */
    public function investors()
    {
        $users = User::where('role', 'investor')
            ->withCount([
                'investments as active_plans_count' => fn($q) => $q->where('status', 'active')
            ])
            ->withSum('investments as total_invested', 'amount')
            ->latest()
            ->paginate(50);

        foreach ($users as $user) {
            $user->total_invested = $user->total_invested ?? 0;
        }

        $totalUsers         = User::where('role', 'investor')->count();
        $totalInvested      = Investment::sum('amount');
        $activeInvestors    = User::where('role', 'investor')->where('status', 'active')->count();
        $pendingWithdrawals = Transaction::where('type', 'withdrawal')->where('status', 'pending')->count();

        return view('admin.investors', compact(
            'users', 'totalUsers', 'totalInvested',
            'activeInvestors', 'pendingWithdrawals'
        ));
    }

    /**
     * Update investor profile (name, email, phone, status, password)
     * Called via AJAX from the slide-over Edit tab
     */
    public function updateUser(Request $request, $id)
    {
        try {
            $user = User::findOrFail($id);

            $validated = $request->validate([
                'name'                  => 'required|string|max:255',
                'email'                 => 'required|email|unique:users,email,' . $user->id,
                'phone'                 => 'nullable|string|max:30',
                'status'                => 'required|in:active,suspended,pending',
                'password'              => 'nullable|min:8|confirmed',
            ]);

            $user->name   = $validated['name'];
            $user->email  = $validated['email'];
            $user->status = $validated['status'];

            if (!empty($validated['phone'])) {
                $user->phone = $validated['phone'];
            }

            if (!empty($validated['password'])) {
                $user->password = Hash::make($validated['password']);
            }

            $user->save();

            $this->logAdminAction('user_updated', 'User', $user->id, [
                'name'   => $user->name,
                'email'  => $user->email,
                'status' => $user->status,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Investor updated successfully.',
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => collect($e->errors())->flatten()->first(),
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update user: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update investor balance (add / deduct / set)
     * Called via AJAX from the slide-over Balance tab
     */
    public function updateBalance(Request $request, $id)
    {
        try {
            $user = User::findOrFail($id);

            $validated = $request->validate([
                'action' => 'required|in:add,deduct,set',
                'amount' => 'required|numeric|min:0.01',
                'note'   => 'nullable|string|max:255',
            ]);

            $oldBalance = $user->balance;
            $amount     = (float) $validated['amount'];

            switch ($validated['action']) {
                case 'add':
                    $user->balance += $amount;
                    break;

                case 'deduct':
                    if ($user->balance < $amount) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Insufficient balance to deduct.',
                        ], 422);
                    }
                    $user->balance -= $amount;
                    break;

                case 'set':
                    $user->balance = $amount;
                    break;
            }

            $user->save();

            // Record it as a transaction so history is complete
            Transaction::create([
                'user_id'     => $user->id,
                'type'        => 'admin_adjustment',
                'method'      => 'Admin',
                'amount'      => $amount,
                'status'      => 'completed',
                'description' => $validated['note'] ?? 'Admin balance adjustment (' . $validated['action'] . ')',
            ]);

            $this->logAdminAction('balance_updated', 'User', $user->id, [
                'action'      => $validated['action'],
                'amount'      => $amount,
                'old_balance' => $oldBalance,
                'new_balance' => $user->balance,
                'note'        => $validated['note'] ?? '',
            ]);

            return response()->json([
                'success'     => true,
                'new_balance' => $user->balance,
                'message'     => 'Balance updated successfully.',
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => collect($e->errors())->flatten()->first(),
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update balance: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get investor transaction history
     * Called via AJAX from the slide-over History tab
     */
    public function userTransactions(Request $request, $id)
    {
        abort_unless($request->ajax(), 403);

        try {
            $user = User::findOrFail($id);

            $transactions = Transaction::where('user_id', $user->id)
                ->latest()
                ->take(20)
                ->get()
                ->map(fn($t) => [
                    'type'      => $t->type,
                    'amount'    => $t->amount,
                    'status'    => $t->status,
                    'plan_name' => $t->plan_name ?? $t->description ?? null,
                    'date'      => $t->created_at->format('M d, Y · h:i A'),
                ]);

            return response()->json([
                'success'      => true,
                'transactions' => $transactions,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load transactions.',
            ], 500);
        }
    }

    /**
     * Send a message / notification to a single investor
     * Called via AJAX from the slide-over Message tab
     */
    public function sendMessage(Request $request)
    {
        try {
            $validated = $request->validate([
                'user_id' => 'required|exists:users,id',
                'type'    => 'required|in:info,success,warning,task',
                'title'   => 'required|string|max:255',
                'message' => 'required|string',
                'channel' => 'nullable|in:in_app,email,both',
            ]);

            $user = User::findOrFail($validated['user_id']);

            // Create in-app notification
            if (in_array($validated['channel'] ?? 'in_app', ['in_app', 'both'])) {
                Notification::create([
                    'user_id' => $user->id,
                    'title'   => $validated['title'],
                    'message' => $validated['message'],
                    'type'    => $validated['type'],
                    'read'    => false,
                ]);
            }

            // Send email if requested
            if (in_array($validated['channel'] ?? 'in_app', ['email', 'both'])) {
                // Mail::to($user->email)->send(new AdminMessageMail($validated['title'], $validated['message']));
            }

            $this->logAdminAction('message_sent', 'User', $user->id, [
                'title'   => $validated['title'],
                'type'    => $validated['type'],
                'channel' => $validated['channel'] ?? 'in_app',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Message sent successfully.',
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => collect($e->errors())->flatten()->first(),
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send message: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get user's current plan based on active investments
     */
    private function getUserCurrentPlan($user)
    {
        $activeInvestment = $user->investments()
            ->where('status', 'active')
            ->latest()
            ->first();

        return $activeInvestment ? $activeInvestment->plan_name : null;
    }

    /**
     * Suspend user
     */
    public function suspendUser($id)
    {
        try {
            $user = User::findOrFail($id);
            $user->update(['status' => 'suspended']);

            $this->logAdminAction('user_suspended', 'User', $id, [
                'user_name'  => $user->name,
                'user_email' => $user->email,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'User suspended successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to suspend user: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Activate user
     */
    public function activateUser($id)
    {
        try {
            $user = User::findOrFail($id);
            $user->update(['status' => 'active']);

            $this->logAdminAction('user_activated', 'User', $id, [
                'user_name'  => $user->name,
                'user_email' => $user->email,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'User activated successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to activate user: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Adjust user balance (existing — kept for backwards compatibility)
     */
    public function adjustBalance(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'action'  => 'required|in:credit,debit',
            'amount'  => 'required|numeric|min:0.01',
            'reason'  => 'required|string|max:500',
        ]);

        try {
            $user       = User::findOrFail($validated['user_id']);
            $oldBalance = $user->balance;

            if ($validated['action'] === 'credit') {
                $user->increment('balance', $validated['amount']);
            } else {
                if ($user->balance < $validated['amount']) {
                    return redirect()->back()->with('error', 'Insufficient balance to debit!');
                }
                $user->decrement('balance', $validated['amount']);
            }

            Transaction::create([
                'user_id'     => $user->id,
                'type'        => 'admin_adjustment',
                'method'      => 'Admin',
                'amount'      => $validated['amount'],
                'status'      => 'completed',
                'description' => $validated['reason'],
            ]);

            $this->logAdminAction('balance_adjusted', 'User', $user->id, [
                'action'      => $validated['action'],
                'amount'      => $validated['amount'],
                'old_balance' => $oldBalance,
                'new_balance' => $user->fresh()->balance,
                'reason'      => $validated['reason'],
            ]);

            return redirect()->back()->with('success', 'Balance adjusted successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to adjust balance: ' . $e->getMessage());
        }
    }

    /**
     * Display tasks management
     */
    public function tasks()
    {
        $tasks = Task::withCount('completions')->latest()->paginate(20);
        return view('admin.tasks', compact('tasks'));
    }

    /**
     * Store new task
     */
    public function storeTask(Request $request)
    {
        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'reward'      => 'required|numeric|min:0.01',
            'expires_at'  => 'nullable|date|after:today',
            'is_active'   => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');

        Task::create($validated);

        $this->logAdminAction('task_created', 'Task', null, $validated);

        return redirect()->back()->with('success', 'Task created successfully!');
    }

    /**
     * Update task
     */
    public function updateTask(Request $request, $id)
    {
        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'reward'      => 'required|numeric|min:0.01',
            'expires_at'  => 'nullable|date',
            'is_active'   => 'boolean',
        ]);

        $task = Task::findOrFail($id);
        $validated['is_active'] = $request->has('is_active');
        $task->update($validated);

        $this->logAdminAction('task_updated', 'Task', $id, $validated);

        return redirect()->back()->with('success', 'Task updated successfully!');
    }

    /**
     * Toggle task status
     */
    public function toggleTaskStatus(Request $request, $id)
    {
        try {
            $task = Task::findOrFail($id);
            $task->update(['is_active' => $request->is_active]);

            $status = $request->is_active ? 'activated' : 'deactivated';

            return response()->json([
                'success' => true,
                'message' => "Task {$status} successfully",
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to toggle task status',
            ], 500);
        }
    }

    /**
     * Delete task
     */
    public function deleteTask($id)
    {
        try {
            $task = Task::findOrFail($id);
            $task->delete();

            $this->logAdminAction('task_deleted', 'Task', $id, [
                'title' => $task->title,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Task deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete task',
            ], 500);
        }
    }

    /**
     * Display withdrawals
     */
    public function withdrawals()
    {
        $withdrawals = Transaction::where('type', 'withdrawal')
            ->with('user')
            ->latest()
            ->paginate(20);

        $pendingCount = Transaction::where('type', 'withdrawal')
            ->where('status', 'pending')
            ->count();

        $approvedToday = Transaction::where('type', 'withdrawal')
            ->where('status', 'completed')
            ->whereDate('processed_at', today())
            ->count();

        return view('admin.withdrawals', compact('withdrawals', 'pendingCount', 'approvedToday'));
    }

    /**
     * Approve withdrawal
     */
    public function approveWithdrawal($id)
    {
        try {
            $withdrawal = Transaction::findOrFail($id);

            if ($withdrawal->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'This withdrawal has already been processed',
                ], 400);
            }

            $withdrawal->update([
                'status'       => 'completed',
                'processed_at' => now(),
                'processed_by' => auth()->id(),
            ]);

            $user = $withdrawal->user;
            $user->decrement('locked_balance', $withdrawal->amount);

            Notification::create([
                'user_id' => $user->id,
                'title'   => 'Withdrawal Approved',
                'message' => 'Your withdrawal request of $' . number_format($withdrawal->amount, 2) . ' has been approved and processed.',
                'type'    => 'success',
            ]);

            $this->logAdminAction('withdrawal_approved', 'Transaction', $id, [
                'amount'  => $withdrawal->amount,
                'user_id' => $user->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Withdrawal approved successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to approve withdrawal: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Decline withdrawal
     */
    public function declineWithdrawal(Request $request, $id)
    {
        $validated = $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        try {
            $withdrawal = Transaction::findOrFail($id);

            if ($withdrawal->status !== 'pending') {
                return redirect()->back()->with('error', 'This withdrawal has already been processed');
            }

            $withdrawal->update([
                'status'       => 'failed',
                'processed_at' => now(),
                'processed_by' => auth()->id(),
                'admin_note'   => $validated['reason'],
            ]);

            $user = $withdrawal->user;
            $user->increment('balance', $withdrawal->amount);
            $user->decrement('locked_balance', $withdrawal->amount);

            Notification::create([
                'user_id' => $user->id,
                'title'   => 'Withdrawal Declined',
                'message' => 'Your withdrawal request of $' . number_format($withdrawal->amount, 2) . ' has been declined. Reason: ' . $validated['reason'],
                'type'    => 'error',
            ]);

            $this->logAdminAction('withdrawal_declined', 'Transaction', $id, [
                'amount'  => $withdrawal->amount,
                'user_id' => $user->id,
                'reason'  => $validated['reason'],
            ]);

            return redirect()->back()->with('success', 'Withdrawal declined successfully');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to decline withdrawal: ' . $e->getMessage());
        }
    }

    /**
     * Display notifications page
     */
    public function notifications()
    {
        $users = User::where('role', 'investor')
            ->where('status', 'active')
            ->select('id', 'name', 'email')
            ->orderBy('name')
            ->get();

        $recentNotifications = Notification::with('user')
            ->select('title', 'message', 'created_at')
            ->selectRaw('COUNT(*) as recipients_count')
            ->groupBy('title', 'message', 'created_at')
            ->latest()
            ->take(10)
            ->get();

        return view('admin.notifications', compact('users', 'recentNotifications'));
    }

    /**
     * Send notification (existing — broadcast to many)
     */
    public function sendNotification(Request $request)
    {
        $validated = $request->validate([
            'title'      => 'required|string|max:255',
            'message'    => 'required|string',
            'type'       => 'required|in:all,selected,single',
            'user_ids'   => 'required_if:type,selected,single|array',
            'user_ids.*' => 'exists:users,id',
            'send_email' => 'boolean',
        ]);

        try {
            if ($validated['type'] === 'all') {
                $users = User::where('role', 'investor')->where('status', 'active')->get();
            } else {
                $users = User::whereIn('id', $validated['user_ids'])->get();
            }

            foreach ($users as $user) {
                Notification::create([
                    'user_id' => $user->id,
                    'title'   => $validated['title'],
                    'message' => $validated['message'],
                    'type'    => 'info',
                ]);

                if ($request->send_email) {
                    // Mail::to($user->email)->send(new NotificationEmail($validated));
                }
            }

            $this->logAdminAction('notification_sent', null, null, [
                'title'             => $validated['title'],
                'recipients_count'  => count($users),
                'type'              => $validated['type'],
            ]);

            return redirect()->back()->with('success', 'Notification sent to ' . count($users) . ' users!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to send notification: ' . $e->getMessage());
        }
    }

    /**
     * Log admin action
     */
    private function logAdminAction($action, $targetType = null, $targetId = null, $details = [])
    {
        if (\Schema::hasTable('admin_logs')) {
            \DB::table('admin_logs')->insert([
                'admin_id'    => auth()->id(),
                'action'      => $action,
                'target_type' => $targetType,
                'target_id'   => $targetId,
                'details'     => json_encode($details),
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
        }
    }
    public function createTask()
{
    return view('admin.tasks.create');
}
}