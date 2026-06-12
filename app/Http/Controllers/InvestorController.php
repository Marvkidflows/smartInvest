<?php
// app/Http/Controllers/InvestorController.php
// COMPLETE CONTROLLER FOR MODERN DASHBOARD

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Transaction;
use App\Models\Investment;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class InvestorController extends Controller
{
    /**
     * Display the investor dashboard
     */
    public function dashboard()
    {
        $user = Auth::user();

        // Get active investments with countdown data
        $activeInvestments = Investment::where('user_id', $user->id)
            ->where('status', 'active')
            ->get()
            ->map(function($investment) {
                $startDate = Carbon::parse($investment->created_at);
                $endDate = $startDate->copy()->addDays($investment->duration_days);
                $now = Carbon::now();
                
                $totalDays = $investment->duration_days;
                $daysElapsed = $startDate->diffInDays($now);
                $daysRemaining = max(0, $endDate->diffInDays($now));
                
                return [
                    'id' => $investment->id,
                    'plan_name' => $investment->plan_name,
                    'tier' => $investment->tier ?? 'Starter',
                    'amount' => $investment->amount,
                    'roi_percentage' => $investment->roi_percentage,
                    'duration_days' => $totalDays,
                    'days_remaining' => $daysRemaining,
                    'progress_percentage' => min(100, ($daysElapsed / $totalDays) * 100),
                    'expected_return' => $investment->amount * ($investment->roi_percentage / 100),
                ];
            });

        // Get recent transactions
        $transactions = Transaction::where('user_id', $user->id)
            ->latest()
            ->take(10)
            ->get()
            ->map(function($tx) {
                return [
                    'id' => $tx->id,
                    'created_at' => $tx->created_at,
                    'type' => $tx->type,
                    'plan_name' => $tx->plan_name ?? '-',
                    'amount' => $tx->amount,
                    'roi_earned' => $tx->roi_earned ?? null,
                    'status' => $tx->status,
                ];
            });

        // Get unread notifications
        $notifications = Notification::where('user_id', $user->id)
            ->latest()
            ->take(10)
            ->get()
            ->map(function($notif) {
                return [
                    'id' => $notif->id,
                    'title' => $notif->title,
                    'message' => $notif->message,
                    'type' => $notif->type ?? 'info',
                    'read' => (bool)$notif->read_at,
                    'created_at' => $notif->created_at,
                ];
            });

        // Calculate total invested
        $totalInvested = Investment::where('user_id', $user->id)
            ->whereIn('status', ['active', 'completed'])
            ->sum('amount');

        // Calculate withdrawable balance (balance that can be withdrawn)
        $withdrawableBalance = $user->balance - ($user->locked_balance ?? 0);

        // Check if today's task is completed
        $taskCompleted = $user->completedTasks()
            ->whereDate('completed_at', today())
            ->exists();

        // Generate daily code
        $dailyCode = 'INV-' . strtoupper(substr(md5($user->id . now()->format('Y-m-d')), 0, 8));

        // Task reward amount
        $taskReward = 25.00;

        // Prepare data for view
        $data = [
            'user' => $user,
            'activePlans' => $activeInvestments->count(),
            'totalInvested' => $totalInvested,
            'activeInvestments' => $activeInvestments,
            'transactions' => $transactions,
            'notifications' => $notifications,
            'dailyCode' => $dailyCode,
            'taskCompleted' => $taskCompleted,
            'taskReward' => $taskReward,
            'totalWithdrawn' => Transaction::where('user_id', $user->id)
                ->where('type', 'withdrawal')
                ->where('status', 'completed')
                ->whereMonth('created_at', now()->month)
                ->sum('amount'),
        ];

        return view('investor.dashboard', $data);
    }

    /**
     * Display tasks page
     */
    public function tasks()
    {
        $user = Auth::user();
        
        $activeTasks = Task::active()
            ->get()
            ->map(function($task) use ($user) {
                $completed = $user->completedTasks()
                    ->where('task_id', $task->id)
                    ->whereDate('completed_at', today())
                    ->exists();
                
                return [
                    'id' => $task->id,
                    'title' => $task->title,
                    'description' => $task->description,
                    'reward' => $task->reward,
                    'completed' => $completed,
                    'expires_at' => now()->endOfDay(),
                ];
            });

        return view('investor.tasks', compact('activeTasks', 'user'));
    }

    /**
     * Complete a task and award reward
     */
    public function completeTask(Request $request)
    {
        $user = Auth::user();

        // Validate the request
        $validated = $request->validate([
            'code' => 'required|string',
        ]);

        // Generate today's expected code
        $expectedCode = 'INV-' . strtoupper(substr(md5($user->id . now()->format('Y-m-d')), 0, 8));

        // Check if code matches
        if ($validated['code'] !== $expectedCode) {
            return back()->with('error', 'Invalid code. Please use today\'s unique code.');
        }

        // Check if already completed today
        if ($user->completedTasks()->whereDate('completed_at', today())->exists()) {
            return back()->with('error', 'You have already completed today\'s task.');
        }

        // Default task reward
        $taskReward = 25.00;

        // Create a task completion record
        $user->completedTasks()->create([
            'task_id' => null, // Daily code task
            'completed_at' => now(),
            'reward' => $taskReward,
        ]);

        // Credit reward to user balance
        $user->increment('balance', $taskReward);

        // Create transaction record
        Transaction::create([
            'user_id' => $user->id,
            'type' => 'task_reward',
            'method' => 'Daily Code',
            'amount' => $taskReward,
            'status' => 'completed',
            'description' => 'Daily task reward - ' . now()->format('Y-m-d'),
        ]);

        // Create notification
        Notification::create([
            'user_id' => $user->id,
            'title' => 'Task Completed!',
            'message' => "You've earned $" . number_format($taskReward, 2) . " from completing today's task.",
            'type' => 'success',
        ]);

        return back()->with('success', "Task completed! $" . number_format($taskReward, 2) . " has been credited to your account.");
    }

    /**
     * Display all transactions
     */
    public function transactions()
    {
        $transactions = Transaction::where('user_id', Auth::id())
            ->latest()
            ->paginate(20);

        return view('investor.transactions', compact('transactions'));
    }

    /**
     * Download transaction receipt
     */
    public function downloadReceipt($transactionId)
    {
        $transaction = Transaction::where('user_id', Auth::id())
            ->findOrFail($transactionId);

        // Generate PDF or return download (implement based on your needs)
        // For now, return a simple response
        return response()->json([
            'success' => true,
            'message' => 'Receipt downloaded',
            'transaction' => $transaction,
        ]);
    }

    /**
     * Download all transactions as CSV/Excel
     */
    public function downloadTransactions(Request $request)
    {
        $transactions = Transaction::where('user_id', Auth::id())
            ->latest()
            ->get();

        // Create CSV
        $filename = 'transactions_' . now()->format('Y-m-d') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function() use ($transactions) {
            $file = fopen('php://output', 'w');
            
            // CSV Header
            fputcsv($file, ['Date', 'Type', 'Plan', 'Amount', 'ROI Earned', 'Status']);
            
            // CSV Rows
            foreach ($transactions as $tx) {
                fputcsv($file, [
                    $tx->created_at->format('Y-m-d H:i'),
                    ucfirst($tx->type),
                    $tx->plan_name ?? '-',
                    '$' . number_format($tx->amount, 2),
                    $tx->roi_earned ? '$' . number_format($tx->roi_earned, 2) : '-',
                    ucfirst($tx->status),
                ]);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Display withdrawals page
     */
    public function withdrawals()
    {
        $withdrawals = Transaction::where('user_id', Auth::id())
            ->where('type', 'withdrawal')
            ->latest()
            ->paginate(20);

        return view('investor.withdrawals', compact('withdrawals'));
    }

    /**
     * Request a withdrawal
     */
    public function requestWithdrawal(Request $request)
    {
        $user = Auth::user();

        // Calculate withdrawable balance
        $withdrawableBalance = $user->balance - ($user->locked_balance ?? 0);

        $validated = $request->validate([
            'amount' => 'required|numeric|min:10|max:' . $withdrawableBalance,
            'method' => 'required|string|in:Bitcoin (BTC),Ethereum (ETH),USDT (TRC20),Bank Transfer',
        ]);

        // Create withdrawal request
        $withdrawal = Transaction::create([
            'user_id' => $user->id,
            'type' => 'withdrawal',
            'method' => $validated['method'],
            'amount' => $validated['amount'],
            'status' => 'pending',
            'description' => 'Withdrawal request via ' . $validated['method'],
        ]);

        // Deduct from balance (put in locked balance until approved)
        $user->decrement('balance', $validated['amount']);
        $user->increment('locked_balance', $validated['amount']);

        // Create notification
        Notification::create([
            'user_id' => $user->id,
            'title' => 'Withdrawal Requested',
            'message' => "Your withdrawal of $" . number_format($validated['amount'], 2) . " is being processed.",
            'type' => 'info',
        ]);

        return back()->with('success', 'Withdrawal request submitted successfully! Processing may take 24-48 hours.');
    }

    /**
     * Display investment plans
     */
    public function plans()
    {
        $plans = [
            [
                'name' => 'Starter Plan',
                'tier' => 'Starter',
                'min_amount' => 1000,
                'max_amount' => 4999,
                'roi_percentage' => 7,
                'duration_days' => 30,
                'risk_level' => 'Low',
                'features' => [
                    'Daily profit distribution',
                    'Basic daily tasks',
                    'Email support',
                    'Withdraw anytime',
                ],
            ],
            [
                'name' => 'Professional Plan',
                'tier' => 'Professional',
                'min_amount' => 5000,
                'max_amount' => 19999,
                'roi_percentage' => 12,
                'duration_days' => 30,
                'risk_level' => 'Medium',
                'features' => [
                    'Everything in Starter',
                    'Priority withdrawals',
                    'Advanced tasks',
                    'Priority support',
                    'Monthly reports',
                ],
                'popular' => true,
            ],
            [
                'name' => 'Elite Plan',
                'tier' => 'Elite',
                'min_amount' => 20000,
                'max_amount' => null,
                'roi_percentage' => 20,
                'duration_days' => 30,
                'risk_level' => 'High',
                'features' => [
                    'Everything in Professional',
                    'Instant withdrawals',
                    'VIP exclusive tasks',
                    'Dedicated manager',
                    'Weekly strategy calls',
                ],
            ],
        ];

        return view('investor.plans', compact('plans'));
    }

    /**
     * Create a new investment
     */
    public function createInvestment(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'plan' => 'required|in:Starter,Professional,Elite',
            'amount' => 'required|numeric|min:1000',
        ]);

        // Check if user has enough balance
        if ($user->balance < $validated['amount']) {
            return back()->with('error', 'Insufficient balance. Please deposit funds first.');
        }

        // Determine ROI based on plan
        $roiRates = [
            'Starter' => 7,
            'Professional' => 12,
            'Elite' => 20,
        ];

        $roiPercentage = $roiRates[$validated['plan']];

        // Create investment
        $investment = Investment::create([
            'user_id' => $user->id,
            'plan_name' => $validated['plan'] . ' Plan',
            'tier' => $validated['plan'],
            'amount' => $validated['amount'],
            'roi_percentage' => $roiPercentage,
            'duration_days' => 30,
            'status' => 'active',
            'expected_return' => $validated['amount'] * ($roiPercentage / 100),
        ]);

        // Deduct from balance
        $user->decrement('balance', $validated['amount']);

        // Create transaction record
        Transaction::create([
            'user_id' => $user->id,
            'type' => 'investment',
            'method' => 'Internal Balance',
            'amount' => $validated['amount'],
            'plan_name' => $validated['plan'] . ' Plan',
            'status' => 'completed',
        ]);

        // Create notification
        Notification::create([
            'user_id' => $user->id,
            'title' => 'Investment Created',
            'message' => "Your {$validated['plan']} Plan investment of $" . number_format($validated['amount'], 2) . " is now active!",
            'type' => 'success',
        ]);

        return redirect()->route('investor.dashboard')
            ->with('success', 'Investment created successfully! Your returns will be credited daily.');
    }

    /**
     * Mark notification as read
     */
    public function markNotificationRead($notificationId)
    {
        $notification = Notification::where('user_id', Auth::id())
            ->findOrFail($notificationId);

        $notification->update(['read_at' => now()]);

        return response()->json(['success' => true]);
    }

    /**
     * Mark all notifications as read
     */
    public function markAllNotificationsRead()
    {
        Notification::where('user_id', Auth::id())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return back()->with('success', 'All notifications marked as read.');
    }
}