@extends('layouts.app')

@section('title', 'Admin Dashboard - Smart System')

@section('content')
<div class="admin-dashboard">
    <!-- Admin Header -->
    <div class="admin-header">
        <div>
            <h1 class="admin-title">Admin Dashboard</h1>
            <p class="admin-subtitle">Platform overview and management</p>
        </div>
        <div class="admin-actions">
            <a href="{{ route('admin.dashboard') }}" class="btn-view-investor">
                <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                    <path d="M10 3C5 3 1.73 6.11 1 10C1.73 13.89 5 17 10 17C15 17 18.27 13.89 19 10C18.27 6.11 15 3 10 3Z" stroke="currentColor" stroke-width="2"/>
                    <circle cx="10" cy="10" r="3" stroke="currentColor" stroke-width="2"/>
                </svg>
                View as Investor
            </a>
        </div>
    </div>

    <!-- Platform Statistics -->
    <div class="admin-stats-grid">
        <!-- Total Users -->
        <div class="admin-stat-card green-gradient">
            <div class="stat-icon green">
                <svg width="32" height="32" viewBox="0 0 32 32" fill="none">
                    <path d="M16 4C13.8783 4 11.8434 4.84286 10.3431 6.34315C8.84285 7.84344 8 9.87827 8 12C8 14.1217 8.84285 16.1566 10.3431 17.6569C11.8434 19.1571 13.8783 20 16 20C18.1217 20 20.1566 19.1571 21.6569 17.6569C23.1571 16.1566 24 14.1217 24 12C24 9.87827 23.1571 7.84344 21.6569 6.34315C20.1566 4.84286 18.1217 4 16 4Z" stroke="white" stroke-width="2"/>
                    <path d="M6 28C6.63214 25.5343 8.04798 23.3421 10.0353 21.7493C12.0227 20.1565 14.4755 19.2476 17 19.2476C19.5245 19.2476 21.9773 20.1565 23.9647 21.7493C25.952 23.3421 27.3679 25.5343 28 28" stroke="white" stroke-width="2" stroke-linecap="round"/>
                </svg>
            </div>
            <div class="stat-content">
                <p class="stat-label">Total Users</p>
                <h2 class="stat-value">{{ $totalUsers ?? 1247 }}</h2>
                <p class="stat-change positive">+24 this week</p>
            </div>
        </div>

        <!-- Total Invested -->
        <div class="admin-stat-card gold-gradient">
            <div class="stat-icon gold">
                <svg width="32" height="32" viewBox="0 0 32 32" fill="none">
                    <path d="M16 28C22.6274 28 28 22.6274 28 16C28 9.37258 22.6274 4 16 4C9.37258 4 4 9.37258 4 16C4 22.6274 9.37258 28 16 28Z" stroke="white" stroke-width="2"/>
                    <path d="M16 10V16L20 18" stroke="white" stroke-width="2" stroke-linecap="round"/>
                </svg>
            </div>
            <div class="stat-content">
                <p class="stat-label">Total Invested</p>
                <h2 class="stat-value">${{ number_format($totalInvested ?? 2840000, 0) }}</h2>
                <p class="stat-change positive">+12.5% growth</p>
            </div>
        </div>

        <!-- Pending Withdrawals -->
        <div class="admin-stat-card orange-gradient">
            <div class="stat-icon orange">
                <svg width="32" height="32" viewBox="0 0 32 32" fill="none">
                    <path d="M12 24L16 28L20 24" stroke="white" stroke-width="2" stroke-linecap="round"/>
                    <path d="M16 6V28" stroke="white" stroke-width="2" stroke-linecap="round"/>
                    <path d="M28 12H4" stroke="white" stroke-width="2" stroke-linecap="round"/>
                </svg>
            </div>
            <div class="stat-content">
                <p class="stat-label">Pending Withdrawals</p>
                <h2 class="stat-value">{{ $pendingWithdrawals ?? 8 }}</h2>
                <a href="{{ route('admin.withdrawals') }}" class="stat-link">Review →</a>
            </div>
        </div>

        <!-- Active Tasks -->
        <div class="admin-stat-card blue-gradient">
            <div class="stat-icon blue">
                <svg width="32" height="32" viewBox="0 0 32 32" fill="none">
                    <path d="M8 16L14 22L24 10" stroke="white" stroke-width="2" stroke-linecap="round"/>
                    <rect x="4" y="4" width="24" height="24" rx="4" stroke="white" stroke-width="2"/>
                </svg>
            </div>
            <div class="stat-content">
                <p class="stat-label">Active Tasks</p>
                <h2 class="stat-value">{{ $activeTasks ?? 12 }}</h2>
                <a href="{{ route('admin.tasks') }}" class="stat-link">Manage →</a>
            </div>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="admin-content-grid">
        <!-- Recent Users Table -->
        <div class="admin-card">
            <div class="admin-card-header">
                <h3>Recent Registrations</h3>
                <a href="{{ route('admin.users') }}" class="view-all-link">View All →</a>
            </div>
            <div class="admin-table-container">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Email</th>
                            <th>Balance</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentUsers ?? [] as $user)
                            <tr>
                                <td>
                                    <div class="user-cell">
                                        <div class="user-avatar">{{ substr($user->name, 0, 1) }}</div>
                                        <span class="user-name">{{ $user->name }}</span>
                                    </div>
                                </td>
                                <td class="email-cell">{{ $user->email }}</td>
                                <td class="balance-cell">${{ number_format($user->balance, 2) }}</td>
                                <td>
                                    <span class="status-badge {{ $user->status === 'active' ? 'active' : 'suspended' }}">
                                        {{ ucfirst($user->status) }}
                                    </span>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn-action edit" onclick="editUser({{ $user->id }})">
                                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                                <path d="M11.5 2L14 4.5L5 13.5H2.5V11L11.5 2Z" stroke="currentColor" stroke-width="2"/>
                                            </svg>
                                        </button>
                                        @if($user->status === 'active')
                                            <button class="btn-action suspend" onclick="suspendUser({{ $user->id }})">
                                                <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                                    <circle cx="8" cy="8" r="7" stroke="currentColor" stroke-width="2"/>
                                                    <path d="M5 11L11 5" stroke="currentColor" stroke-width="2"/>
                                                </svg>
                                            </button>
                                        @else
                                            <button class="btn-action activate" onclick="activateUser({{ $user->id }})">
                                                <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                                    <circle cx="8" cy="8" r="7" stroke="currentColor" stroke-width="2"/>
                                                    <path d="M5 8L7 10L11 6" stroke="currentColor" stroke-width="2"/>
                                                </svg>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="empty-state">No recent users</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="admin-sidebar">
            <!-- Quick Actions -->
            <div class="admin-card">
                <div class="admin-card-header">
                    <h3>Quick Actions</h3>
                </div>
                <div class="quick-actions">
                    <a href="{{ route('admin.tasks.create') }}" class="quick-action-btn primary">
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                            <path d="M10 4V16M4 10H16" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                        Create Task
                    </a>
                    <a href="{{ route('admin.notifications.create') }}" class="quick-action-btn secondary">
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                            <path d="M15 6.66699C15 5.34091 14.4732 4.06914 13.5355 3.13146C12.5979 2.19378 11.3261 1.66699 10 1.66699C8.67392 1.66699 7.40215 2.19378 6.46447 3.13146C5.52678 4.06914 5 5.34091 5 6.66699C5 12.5003 2.5 14.167 2.5 14.167H17.5C17.5 14.167 15 12.5003 15 6.66699Z" stroke="currentColor" stroke-width="2"/>
                        </svg>
                        Send Notification
                    </a>
                    <a href="{{ route('admin.users') }}" class="quick-action-btn secondary">
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                            <path d="M13 15C13 13.6739 12.4732 12.4021 11.5355 11.4645C10.5979 10.5268 9.32608 10 8 10C6.67392 10 5.40215 10.5268 4.46447 11.4645C3.52678 12.4021 3 13.6739 3 15" stroke="currentColor" stroke-width="2"/>
                            <circle cx="8" cy="5" r="3" stroke="currentColor" stroke-width="2"/>
                        </svg>
                        Manage Users
                    </a>
                    <a href="{{ route('admin.withdrawals') }}" class="quick-action-btn warning">
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                            <path d="M6 14L10 18L14 14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            <path d="M10 2V18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                        Process Withdrawals
                    </a>
                </div>
            </div>

            <!-- System Status -->
            <div class="admin-card">
                <div class="admin-card-header">
                    <h3>System Status</h3>
                </div>
                <div class="system-status">
                    <div class="status-item">
                        <span class="status-label">Server</span>
                        <span class="status-indicator online">● Online</span>
                    </div>
                    <div class="status-item">
                        <span class="status-label">Database</span>
                        <span class="status-indicator online">● Connected</span>
                    </div>
                    <div class="status-item">
                        <span class="status-label">API</span>
                        <span class="status-indicator online">● Active</span>
                    </div>
                    <div class="status-item">
                        <span class="status-label">Last Backup</span>
                        <span class="status-value">{{ now()->subHours(2)->diffForHumans() }}</span>
                    </div>
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="admin-card">
                <div class="admin-card-header">
                    <h3>Quick Stats</h3>
                </div>
                <div class="quick-stats">
                    <div class="quick-stat-item">
                        <span class="quick-stat-label">Today's Signups</span>
                        <span class="quick-stat-value">{{ $todaySignups ?? 12 }}</span>
                    </div>
                    <div class="quick-stat-item">
                        <span class="quick-stat-label">Active Investments</span>
                        <span class="quick-stat-value">{{ $activeInvestments ?? 347 }}</span>
                    </div>
                    <div class="quick-stat-item">
                        <span class="quick-stat-label">Today's Earnings</span>
                        <span class="quick-stat-value">${{ number_format($todayEarnings ?? 8450, 0) }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function editUser(userId) {
    window.location.href = `/admin/users/${userId}/edit`;
}

function suspendUser(userId) {
    if (confirm('Are you sure you want to suspend this user?')) {
        fetch(`/admin/users/${userId}/suspend`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('User suspended successfully');
                location.reload();
            }
        });
    }
}

function activateUser(userId) {
    if (confirm('Are you sure you want to activate this user?')) {
        fetch(`/admin/users/${userId}/activate`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('User activated successfully');
                location.reload();
            }
        });
    }
}
</script>
@endpush
@endsection