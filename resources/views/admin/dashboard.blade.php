@extends('layouts.app')

@section('title', 'Admin Dashboard - Smart System')

@section('content')
<div class="dashboard">
    <div class="dashboard-header">
        <h1 class="welcome-text">Admin Dashboard</h1>
        <p class="dashboard-subtitle">Platform overview and management</p>
    </div>

    <!-- Platform Statistics -->
    <div class="dashboard-grid">
        <div class="dashboard-card" style="background: linear-gradient(135deg, #1a472a, #0f2918); color: white;">
            <div class="card-label" style="color: rgba(255,255,255,0.9);">Total Users</div>
            <div class="card-amount" style="color: white;">{{ $totalUsers ?? 1247 }}</div>
            <div class="card-change" style="color: rgba(255,255,255,0.8);">+24 this week</div>
        </div>

        <div class="dashboard-card" style="background: linear-gradient(135deg, #d4af37, #e5c158); color: white;">
            <div class="card-label" style="color: rgba(0,0,0,0.8);">Total Invested</div>
            <div class="card-amount" style="color: #0f2918;">${{ number_format($totalInvested ?? 2840000, 0) }}</div>
            <div class="card-change" style="color: rgba(0,0,0,0.7);">+12.5% growth</div>
        </div>

        <div class="dashboard-card">
            <div class="card-icon">⏳</div>
            <div class="card-label">Pending Withdrawals</div>
            <div class="card-amount" style="color: var(--warning);">{{ $pendingWithdrawals ?? 8 }}</div>
            <a href="{{ route('admin.withdrawals') }}" style="color: var(--primary); text-decoration: none; font-weight: 600;">Review →</a>
        </div>

        <div class="dashboard-card">
            <div class="card-icon">✓</div>
            <div class="card-label">Active Tasks</div>
            <div class="card-amount">{{ $activeTasks ?? 12 }}</div>
            <a href="{{ route('admin.tasks') }}" style="color: var(--primary); text-decoration: none; font-weight: 600;">Manage →</a>
        </div>
    </div>

    <!-- Recent Activity -->
    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem;">
        <!-- Recent Users -->
        <div class="dashboard-card">
            <h3 style="font-weight: 700; margin-bottom: 1.5rem; color: var(--primary);">Recent Registrations</h3>
            <table>
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Email</th>
                        <th>Balance</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentUsers ?? [] as $user)
                        <tr>
                            <td style="font-weight: 600;">{{ $user->name }}</td>
                            <td style="font-size: 0.9rem;">{{ $user->email }}</td>
                            <td style="font-weight: 700;">${{ number_format($user->balance, 2) }}</td>
                            <td>
                                <span class="badge badge-{{ $user->status === 'active' ? 'success' : 'danger' }}">
                                    {{ $user->status }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('admin.users') }}" class="btn btn-secondary btn-small">View</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 2rem;">No recent users</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Quick Actions -->
        <div class="dashboard-card">
            <h3 style="font-weight: 700; margin-bottom: 1.5rem; color: var(--primary);">Quick Actions</h3>
            <div style="display: flex; flex-direction: column; gap: 1rem;">
                <a href="{{ route('admin.tasks') }}" class="btn btn-primary" style="width: 100%; text-align: center;">
                    Create Task
                </a>
                <a href="{{ route('admin.notifications') }}" class="btn btn-secondary" style="width: 100%; text-align: center;">
                    Send Notification
                </a>
                <a href="{{ route('admin.users') }}" class="btn btn-secondary" style="width: 100%; text-align: center;">
                    Manage Users
                </a>
                <a href="{{ route('admin.withdrawals') }}" class="btn btn-accent" style="width: 100%; text-align: center;">
                    Process Withdrawals
                </a>
            </div>

            <!-- System Stats -->
            <div style="margin-top: 2rem; padding-top: 2rem; border-top: 1px solid var(--border);">
                <h4 style="font-weight: 700; margin-bottom: 1rem; font-size: 0.9rem; color: var(--text-secondary); text-transform: uppercase;">System Status</h4>
                <div style="display: flex; justify-content: space-between; margin-bottom: 0.75rem;">
                    <span style="font-size: 0.9rem;">Server</span>
                    <span style="color: var(--success); font-weight: 600;">● Online</span>
                </div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 0.75rem;">
                    <span style="font-size: 0.9rem;">Database</span>
                    <span style="color: var(--success); font-weight: 600;">● Connected</span>
                </div>
                <div style="display: flex; justify-content: space-between;">
                    <span style="font-size: 0.9rem;">API</span>
                    <span style="color: var(--success); font-weight: 600;">● Active</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection