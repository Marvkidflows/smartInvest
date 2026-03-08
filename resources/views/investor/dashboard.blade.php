@extends('layouts.app')

@section('title', 'Dashboard - Smart System Investment')

@section('content')
@push('styles')
<style>
       /* =====================================================
   MODERN DASHBOARD STYLES
   Add this to resources/views/layouts/styles.blade.php
   ===================================================== */

/* Dashboard Container */
.dashboard-header-enhanced {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2.5rem;
    padding: 2rem;
    background: white;
    border-radius: 20px;
    border: 1px solid #E5E7EB;
}

.profile-section {
    display: flex;
    align-items: center;
    gap: 1.5rem;
}

.profile-avatar-wrapper {
    position: relative;
}

.profile-avatar,
.profile-avatar-placeholder {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    object-fit: cover;
}

.profile-avatar-placeholder {
    background: linear-gradient(135deg, #2563EB 0%, #1E3A8A 100%);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.75rem;
    font-weight: 700;
}

.avatar-edit-btn {
    position: absolute;
    bottom: 0;
    right: 0;
    width: 32px;
    height: 32px;
    background: white;
    border: 2px solid #2563EB;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    color: #2563EB;
    transition: all 0.2s ease;
}

.avatar-edit-btn:hover {
    background: #2563EB;
    color: white;
}

.profile-info {
    flex: 1;
}

.profile-name-row {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 0.5rem;
}

.profile-name {
    font-size: 1.75rem;
    font-weight: 700;
    color: #1E3A8A;
    font-family: 'Crimson Pro', serif;
    margin: 0;
}

.status-indicator {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.375rem 0.75rem;
    border-radius: 20px;
    font-size: 0.875rem;
    font-weight: 600;
}

.status-indicator.active {
    background: #D1FAE5;
    color: #065F46;
}

.status-indicator.inactive {
    background: #FEE2E2;
    color: #991B1B;
}

.status-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: currentColor;
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.5; }
}

.profile-email {
    color: #64748B;
    font-size: 0.95rem;
    margin-bottom: 0.25rem;
}

.profile-date {
    color: #9CA3AF;
    font-size: 0.875rem;
}

.btn-edit-profile {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1.5rem;
    background: white;
    border: 2px solid #2563EB;
    border-radius: 10px;
    color: #2563EB;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
}

.btn-edit-profile:hover {
    background: #2563EB;
    color: white;
}

/* Overview Grid */
.overview-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.overview-card {
    background: white;
    border-radius: 16px;
    padding: 1.5rem;
    border: 1px solid #E5E7EB;
    transition: all 0.3s ease;
}

.overview-card:hover {
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
    transform: translateY(-4px);
}

.overview-card.gradient-blue {
    background: linear-gradient(135deg, #1E3A8A 0%, #2563EB 100%);
    border: none;
    color: white;
}

.card-icon-wrapper {
    width: 64px;
    height: 64px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 1rem;
}

.card-icon-wrapper.blue {
    background: rgba(255, 255, 255, 0.2);
    color: white;
}

.card-icon-wrapper.green {
    background: linear-gradient(135deg, #10B981 0%, #059669 100%);
    color: white;
}

.card-icon-wrapper.purple {
    background: linear-gradient(135deg, #8B5CF6 0%, #7C3AED 100%);
    color: white;
}

.card-icon-wrapper.orange {
    background: linear-gradient(135deg, #F59E0B 0%, #D97706 100%);
    color: white;
}

.card-content-white p,
.card-content-white h2 {
    color: white;
}

.card-label-white,
.card-hint-white {
    opacity: 0.9;
}

.card-label {
    font-size: 0.875rem;
    color: #64748B;
    margin-bottom: 0.5rem;
}

.card-label-white {
    font-size: 0.875rem;
    margin-bottom: 0.5rem;
}

.card-amount {
    font-size: 2rem;
    font-weight: 700;
    color: #1E3A8A;
    font-family: 'Crimson Pro', serif;
    margin-bottom: 0.5rem;
}

.card-amount-white {
    font-size: 2rem;
    font-weight: 700;
    font-family: 'Crimson Pro', serif;
    margin-bottom: 0.5rem;
}

.card-hint-white {
    font-size: 0.875rem;
}

.card-change {
    display: flex;
    align-items: center;
    gap: 0.25rem;
    font-size: 0.875rem;
    font-weight: 500;
}

.card-change.positive {
    color: #10B981;
}

.card-change.negative {
    color: #EF4444;
}

.quick-action-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    background: #2563EB;
    color: white;
    border-radius: 8px;
    border: none;
    font-weight: 600;
    font-size: 0.875rem;
    cursor: pointer;
    transition: all 0.2s ease;
    margin-top: 1rem;
}

.quick-action-btn:hover {
    background: #1E3A8A;
}

.quick-action-link {
    color: #2563EB;
    font-weight: 600;
    font-size: 0.875rem;
    text-decoration: none;
}

.quick-action-link:hover {
    text-decoration: underline;
}

/* Dashboard Row */
.dashboard-row {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 1.5rem;
    margin-bottom: 2rem;
}

/* Dashboard Card Modern */
.dashboard-card-modern {
    background: white;
    border-radius: 16px;
    padding: 2rem;
    border: 1px solid #E5E7EB;
}

.card-header-modern {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
}

.card-header-modern h3 {
    font-size: 1.25rem;
    font-weight: 700;
    color: #1E3A8A;
}

.period-selector,
.filter-select {
    padding: 0.5rem 1rem;
    border: 1px solid #E5E7EB;
    border-radius: 8px;
    font-size: 0.875rem;
    background: white;
    cursor: pointer;
}

.view-all-link {
    color: #2563EB;
    font-size: 0.875rem;
    font-weight: 600;
    text-decoration: none;
}

.view-all-link:hover {
    text-decoration: underline;
}

/* ROI Stats */
.roi-stats {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1rem;
    margin-bottom: 1.5rem;
}

.roi-stat-item {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.roi-label {
    font-size: 0.875rem;
    color: #64748B;
}

.roi-value {
    font-size: 1.5rem;
    font-weight: 700;
    color: #1E3A8A;
    font-family: 'Crimson Pro', serif;
}

.roi-value.current {
    color: #10B981;
}

/* Progress Bar */
.progress-bar-container {
    height: 12px;
    background: #E5E7EB;
    border-radius: 12px;
    overflow: hidden;
    margin-bottom: 2rem;
    position: relative;
}

.progress-bar-fill {
    height: 100%;
    background: linear-gradient(90deg, #10B981 0%, #059669 100%);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: flex-end;
    padding-right: 0.5rem;
    transition: width 0.5s ease;
}

.progress-label {
    font-size: 0.75rem;
    font-weight: 700;
    color: white;
}

/* Daily Task Card */
.daily-task-card {
    background: linear-gradient(135deg, #F8FAFC 0%, #EFF6FF 100%);
    border: 1px solid #DBEAFE;
}

.task-date {
    font-size: 0.875rem;
    color: #2563EB;
    font-weight: 600;
}

.daily-code-box {
    background: white;
    padding: 1.5rem;
    border-radius: 12px;
    border: 2px dashed #2563EB;
    margin-bottom: 1.5rem;
}

.code-label {
    font-size: 0.875rem;
    color: #64748B;
    margin-bottom: 0.75rem;
}

.code-display {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.code-text {
    font-size: 1.5rem;
    font-weight: 700;
    color: #1E3A8A;
    font-family: 'Courier New', monospace;
    letter-spacing: 2px;
}

.code-copy-btn {
    padding: 0.5rem;
    background: #EFF6FF;
    border: 1px solid #DBEAFE;
    border-radius: 8px;
    cursor: pointer;
    color: #2563EB;
    transition: all 0.2s ease;
}

.code-copy-btn:hover {
    background: #DBEAFE;
}

/* Countdown Timer */
.countdown-timer {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem;
    background: #FEE2E2;
    border-radius: 8px;
    margin-bottom: 1rem;
}

.timer-label {
    font-size: 0.875rem;
    color: #991B1B;
    font-weight: 500;
}

.timer-value {
    font-size: 1.25rem;
    font-weight: 700;
    color: #DC2626;
    font-family: 'Courier New', monospace;
}

/* Task Status */
.task-status {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 1.5rem;
}

.status-label {
    font-size: 0.875rem;
    color: #64748B;
}

.status-badge {
    padding: 0.25rem 0.75rem;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 600;
}

.status-badge.pending {
    background: #FEF3C7;
    color: #92400E;
}

.status-badge.completed {
    background: #D1FAE5;
    color: #065F46;
}

/* Activate Task Button */
.btn-activate-task {
    width: 100%;
    padding: 1rem;
    background: linear-gradient(135deg, #2563EB 0%, #1E3A8A 100%);
    color: white;
    border-radius: 8px;
    border: none;
    font-weight: 600;
    font-size: 1rem;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    transition: all 0.3s ease;
}

.btn-activate-task:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(37, 99, 235, 0.3);
}

/* Task Completed Message */
.task-completed-message {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 1rem;
    padding: 2rem;
    text-align: center;
}

.task-completed-message p {
    color: #065F46;
    font-weight: 600;
}

/* Countdown Grid */
.countdown-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1.5rem;
}

.countdown-card {
    background: #F8FAFC;
    padding: 1.5rem;
    border-radius: 12px;
    border: 1px solid #E5E7EB;
}

.countdown-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
}

.countdown-header h4 {
    font-weight: 700;
    color: #1E3A8A;
}

.plan-badge {
    padding: 0.25rem 0.75rem;
    background: #DBEAFE;
    color: #1E3A8A;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 600;
}

.countdown-amount {
    font-size: 1.75rem;
    font-weight: 700;
    color: #10B981;
    font-family: 'Crimson Pro', serif;
    margin-bottom: 1rem;
}

.countdown-info {
    display: flex;
    gap: 2rem;
    margin-bottom: 1rem;
}

.info-item {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.info-label {
    font-size: 0.75rem;
    color: #64748B;
}

.info-value {
    font-weight: 700;
    color: #1E3A8A;
}


.countdown-progress {
    height: 6px;
    background: #E5E7EB;
    border-radius: 6px;
    overflow: hidden;
    margin-bottom: 1rem;
}

.countdown-bar {
    height: 100%;
    background: linear-gradient(90deg, #2563EB 0%, #1E3A8A 100%);
    border-radius: 6px;
    transition: width 0.5s ease;
}

.countdown-time {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: #64748B;
    font-size: 0.875rem;
}

/* Empty State */
.empty-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 3rem;
    text-align: center;
    gap: 1rem;
}

.empty-state p {
    color: #64748B;
    font-size: 1rem;
}

.btn-start-investing {
    padding: 0.75rem 1.5rem;
    background: #2563EB;
    color: white;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 600;
    transition: all 0.2s ease;
}

.btn-start-investing:hover {
    background: #1E3A8A;
}

/* Modern Table */
.table-container {
    overflow-x: auto;
}

.modern-table {
    width: 100%;
    border-collapse: collapse;
}

.modern-table thead tr {
    background: #F8FAFC;
}

.modern-table th {
    padding: 1rem;
    text-align: left;
    font-weight: 600;
    font-size: 0.875rem;
    color: #64748B;
    border-bottom: 1px solid #E5E7EB;
}

.modern-table td {
    padding: 1rem;
    border-bottom: 1px solid #F3F4F6;
}

.modern-table tbody tr:hover {
    background: #F8FAFC;
}

.date-cell {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.date-main {
    font-weight: 600;
    color: #1E3A8A;
}

.date-time {
    font-size: 0.75rem;
    color: #64748B;
}

.type-cell {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.type-icon {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.type-icon.deposit {
    background: #D1FAE5;
    color: #065F46;
}

.type-icon.withdrawal {
    background: #FEE2E2;
    color: #991B1B;
}

.type-icon.profit {
    background: #DBEAFE;
    color: #1E3A8A;
}

.amount {
    font-weight: 700;
}

.amount.positive {
    color: #10B981;
}

.amount.negative {
    color: #1E3A8A;
}

.roi-earned {
    color: #10B981;
    font-weight: 600;
}

.text-muted {
    color: #9CA3AF;
}

.status-pill {
    padding: 0.375rem 0.75rem;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 600;
}

.status-pill.completed {
    background: #D1FAE5;
    color: #065F46;
}

.status-pill.pending {
    background: #FEF3C7;
    color: #92400E;
}

.status-pill.failed {
    background: #FEE2E2;
    color: #991B1B;
}

.btn-action {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    padding: 0.5rem 0.75rem;
    background: #F3F4F6;
    border: 1px solid #E5E7EB;
    border-radius: 6px;
    font-size: 0.875rem;
    font-weight: 500;
    cursor: pointer;
    color: #64748B;
    transition: all 0.2s ease;
}

.btn-action:hover {
    background: #2563EB;
    color: white;
    border-color: #2563EB;
}

.empty-table {
    text-align: center;
    padding: 3rem !important;
}

.empty-state-small p {
    color: #64748B;
}

.table-footer {
    padding: 1.5rem;
    text-align: center;
    border-top: 1px solid #E5E7EB;
}

.view-all-transactions {
    color: #2563EB;
    font-weight: 600;
    text-decoration: none;
}

.view-all-transactions:hover {
    text-decoration: underline;
}

.btn-icon-small {
    padding: 0.5rem;
    background: #F3F4F6;
    border: 1px solid #E5E7EB;
    border-radius: 6px;
    cursor: pointer;
    color: #64748B;
    transition: all 0.2s ease;
}

.btn-icon-small:hover {
    background: #2563EB;
    color: white;
}

/* Notification Panel */
.notification-panel {
    position: fixed;
    top: 0;
    right: -400px;
    width: 400px;
    height: 100vh;
    background: white;
    box-shadow: -4px 0 20px rgba(0, 0, 0, 0.1);
    transition: right 0.3s ease;
    z-index: 1000;
}

.notification-panel.active {
    right: 0;
}

.notification-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1.5rem;
    border-bottom: 1px solid #E5E7EB;
}

.notification-header h3 {
    font-weight: 700;
    color: #1E3A8A;
}

.close-btn {
    padding: 0.5rem;
    background: #F3F4F6;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    color: #64748B;
}

.close-btn:hover {
    background: #E5E7EB;
}

.notification-list {
    height: calc(100vh - 80px);
    overflow-y: auto;
    padding: 1rem;
}

.notification-item {
    display: flex;
    gap: 1rem;
    padding: 1rem;
    border-radius: 8px;
    margin-bottom: 0.75rem;
    transition: background 0.2s ease;
}

.notification-item.unread {
    background: #EFF6FF;
}

.notification-item:hover {
    background: #F8FAFC;
}

.notification-icon {
    width: 40px;
    height: 40px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.notification-icon.info {
    background: #DBEAFE;
    color: #1E3A8A;
}

.notification-icon.success {
    background: #D1FAE5;
    color: #065F46;
}

.notification-icon.warning {
    background: #FEF3C7;
    color: #92400E;
}

.notification-content {
    flex: 1;
}

.notification-title {
    font-weight: 600;
    color: #1E3A8A;
    margin-bottom: 0.25rem;
}

.notification-message {
    font-size: 0.875rem;
    color: #64748B;
    margin-bottom: 0.5rem;
}

.notification-time {
    font-size: 0.75rem;
    color: #9CA3AF;
}

.empty-notifications {
    text-align: center;
    padding: 3rem;
    color: #64748B;
}
/* =====================================================
   ROI PROGRESS BAR - REDESIGNED (CLEAN & PROFESSIONAL)
   Add this to your layouts/styles.blade.php
   ===================================================== */

/* ROI Progress Card */
.roi-progress-card {
    background: white;
    border-radius: 20px;
    padding: 2.5rem;
    border: 1px solid #E5E7EB;
    margin-bottom: 2rem;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04);
}

.roi-card-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 2.5rem;
}

.roi-card-title {
    font-size: 1.5rem;
    font-weight: 700;
    color: #1E3A8A;
    margin-bottom: 0.5rem;
    font-family: 'Crimson Pro', serif;
}

.roi-card-subtitle {
    color: #64748B;
    font-size: 0.95rem;
}

.period-selector-modern {
    padding: 0.75rem 1.25rem;
    border: 2px solid #E5E7EB;
    border-radius: 10px;
    font-size: 0.9rem;
    background: white;
    cursor: pointer;
    font-weight: 500;
    color: #1E3A8A;
    transition: all 0.2s ease;
}

.period-selector-modern:hover {
    border-color: #2563EB;
}

.period-selector-modern:focus {
    outline: none;
    border-color: #2563EB;
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
}

/* ROI Metrics Grid */
.roi-metrics-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1.5rem;
    margin-bottom: 2.5rem;
}

.roi-metric {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1.5rem;
    background: #F8FAFC;
    border-radius: 12px;
    border: 1px solid #E5E7EB;
    transition: all 0.2s ease;
}

.roi-metric:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
}

.roi-metric-icon {
    width: 48px;
    height: 48px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.roi-metric-icon.blue-icon {
    background: linear-gradient(135deg, #DBEAFE 0%, #BFDBFE 100%);
    color: #1E3A8A;
}

.roi-metric-icon.green-icon {
    background: linear-gradient(135deg, #D1FAE5 0%, #A7F3D0 100%);
    color: #065F46;
}

.roi-metric-icon.gold-icon {
    background: linear-gradient(135deg, #FEF3C7 0%, #FDE68A 100%);
    color: #92400E;
}

.roi-metric-icon.purple-icon {
    background: linear-gradient(135deg, #EDE9FE 0%, #DDD6FE 100%);
    color: #5B21B6;
}

.roi-metric-content {
    flex: 1;
}

.roi-metric-label {
    font-size: 0.875rem;
    color: #64748B;
    margin-bottom: 0.25rem;
}

.roi-metric-value {
    font-size: 1.75rem;
    font-weight: 700;
    color: #1E3A8A;
    font-family: 'Crimson Pro', serif;
    margin-bottom: 0.25rem;
}

.roi-metric-value.roi-expected {
    color: #2563EB;
}

.roi-metric-value.roi-current {
    color: #10B981;
}

.roi-metric-value.roi-remaining {
    color: #8B5CF6;
}

.roi-amount-small {
    font-size: 0.8rem;
    color: #10B981;
    font-weight: 600;
}

/* ROI Progress Bar */
.roi-progress-wrapper {
    background: #F8FAFC;
    padding: 2rem;
    border-radius: 12px;
    margin-bottom: 1.5rem;
}

.roi-progress-info {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
}

.roi-progress-label {
    font-weight: 600;
    color: #1E3A8A;
    font-size: 0.95rem;
}

.roi-progress-percentage {
    font-size: 1.5rem;
    font-weight: 700;
    color: #10B981;
    font-family: 'Crimson Pro', serif;
}

.roi-progress-bar-modern {
    position: relative;
    height: 24px;
    background: #E5E7EB;
    border-radius: 12px;
    overflow: visible;
    margin-bottom: 0.75rem;
}

.roi-progress-fill {
    height: 100%;
    background: linear-gradient(90deg, #10B981 0%, #059669 100%);
    border-radius: 12px;
    position: relative;
    transition: width 1s ease;
    display: flex;
    align-items: center;
    justify-content: flex-end;
    padding-right: 1rem;
}

.roi-progress-marker {
    background: white;
    color: #10B981;
    padding: 0.25rem 0.75rem;
    border-radius: 6px;
    font-weight: 700;
    font-size: 0.875rem;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    position: absolute;
    right: -10px;
    top: -30px;
}

.roi-progress-marker::after {
    content: '';
    position: absolute;
    bottom: -4px;
    left: 50%;
    transform: translateX(-50%);
    width: 0;
    height: 0;
    border-left: 5px solid transparent;
    border-right: 5px solid transparent;
    border-top: 5px solid white;
}

.roi-target-marker {
    position: absolute;
    top: -35px;
    transform: translateX(-50%);
    background: #2563EB;
    color: white;
    padding: 0.25rem 0.75rem;
    border-radius: 6px;
    font-weight: 700;
    font-size: 0.875rem;
    white-space: nowrap;
}

.roi-target-marker::after {
    content: '';
    position: absolute;
    bottom: -4px;
    left: 50%;
    transform: translateX(-50%);
    width: 0;
    height: 0;
    border-left: 5px solid transparent;
    border-right: 5px solid transparent;
    border-top: 5px solid #2563EB;
}

.roi-progress-labels {
    display: flex;
    justify-content: space-between;
    font-size: 0.875rem;
    color: #64748B;
    font-weight: 500;
}

.roi-start-label,
.roi-end-label {
    padding: 0 0.5rem;
}

/* ROI Status Banner */
.roi-status-banner {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1.25rem 1.5rem;
    background: linear-gradient(135deg, #D1FAE5 0%, #A7F3D0 100%);
    border-radius: 12px;
    border: 1px solid #10B981;
    color: #065F46;
    font-weight: 500;
}

.roi-status-banner svg {
    flex-shrink: 0;
}

/* Responsive Design */
@media (max-width: 1200px) {
    .roi-metrics-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 768px) {
    .roi-progress-card {
        padding: 1.5rem;
    }
    
    .roi-card-header {
        flex-direction: column;
        gap: 1rem;
    }
    
    .period-selector-modern {
        width: 100%;
    }
    
    .roi-metrics-grid {
        grid-template-columns: 1fr;
    }
    
    .roi-metric {
        padding: 1rem;
    }
    
    .roi-metric-value {
        font-size: 1.5rem;
    }
    
    .roi-progress-wrapper {
        padding: 1.5rem;
    }
}

/* Responsive */
@media (max-width: 1200px) {
    .overview-grid {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .countdown-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 768px) {
    .modern-dashboard {
        padding: 1rem;
    }
    
    .dashboard-header-modern {
        flex-direction: column;
        align-items: flex-start;
        gap: 1rem;
    }
    
    .overview-grid,
    .countdown-grid {
        grid-template-columns: 1fr;
    }
    
    .dashboard-row {
        grid-template-columns: 1fr;
    }
    
    .notification-panel {
        width: 100%;
        right: -100%;
    }
}
/* =====================================================
   ENHANCED INVESTOR DASHBOARD STYLES
   Add this to your layouts/styles.blade.php
   ===================================================== */

/* Enhanced Dashboard Header with Profile */
.dashboard-header-enhanced {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2.5rem;
    padding: 2rem;
    background: white;
    border-radius: 20px;
    border: 1px solid #E5E7EB;
}

.profile-section {
    display: flex;
    align-items: center;
    gap: 1.5rem;
}

.profile-avatar-wrapper {
    position: relative;
}

.profile-avatar,
.profile-avatar-placeholder {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    object-fit: cover;
}

.profile-avatar-placeholder {
    background: linear-gradient(135deg, #2563EB 0%, #1E3A8A 100%);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.75rem;
    font-weight: 700;
}

.avatar-edit-btn {
    position: absolute;
    bottom: 0;
    right: 0;
    width: 32px;
    height: 32px;
    background: white;
    border: 2px solid #2563EB;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    color: #2563EB;
    transition: all 0.2s ease;
}

.avatar-edit-btn:hover {
    background: #2563EB;
    color: white;
}

.profile-info {
    flex: 1;
}

.profile-name-row {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 0.5rem;
}

.profile-name {
    font-size: 1.75rem;
    font-weight: 700;
    color: #1E3A8A;
    font-family: 'Crimson Pro', serif;
    margin: 0;
}

.status-indicator {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.375rem 0.75rem;
    border-radius: 20px;
    font-size: 0.875rem;
    font-weight: 600;
}

.status-indicator.active {
    background: #D1FAE5;
    color: #065F46;
}

.status-indicator.inactive {
    background: #FEE2E2;
    color: #991B1B;
}

.status-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: currentColor;
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.5; }
}

.profile-email {
    color: #64748B;
    font-size: 0.95rem;
    margin-bottom: 0.25rem;
}

.profile-date {
    color: #9CA3AF;
    font-size: 0.875rem;
}

.btn-edit-profile {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1.5rem;
    background: white;
    border: 2px solid #2563EB;
    border-radius: 10px;
    color: #2563EB;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
}

.btn-edit-profile:hover {
    background: #2563EB;
    color: white;
}

/* Portfolio Chart Card */
.portfolio-chart-card {
    background: white;
    border-radius: 20px;
    padding: 2.5rem;
    border: 1px solid #E5E7EB;
    margin-bottom: 2rem;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04);
}

.chart-card-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 2rem;
}

.chart-card-title {
    font-size: 1.5rem;
    font-weight: 700;
    color: #1E3A8A;
    margin-bottom: 0.5rem;
    font-family: 'Crimson Pro', serif;
}

.chart-card-subtitle {
    color: #64748B;
    font-size: 0.95rem;
}

.chart-tabs {
    display: flex;
    gap: 0.5rem;
    background: #F8FAFC;
    padding: 0.375rem;
    border-radius: 10px;
}

.chart-tab {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.625rem 1.25rem;
    background: transparent;
    border: none;
    border-radius: 8px;
    color: #64748B;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
}

.chart-tab.active {
    background: white;
    color: #2563EB;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}

.chart-tab:hover:not(.active) {
    background: rgba(255, 255, 255, 0.5);
}

.portfolio-content {
    display: grid;
    grid-template-columns: 1.2fr 1fr;
    gap: 3rem;
}

.chart-container {
    position: relative;
    height: 350px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.portfolio-breakdown {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.breakdown-title {
    font-weight: 700;
    color: #1E3A8A;
    font-size: 1.1rem;
    margin-bottom: 0.5rem;
}

.asset-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem;
    background: #F8FAFC;
    border-radius: 10px;
    border: 1px solid #E5E7EB;
    transition: all 0.2s ease;
}

.asset-item:hover {
    background: white;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.06);
}

.asset-info {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.asset-color {
    width: 16px;
    height: 16px;
    border-radius: 4px;
}

.asset-name {
    font-weight: 600;
    color: #1E3A8A;
    font-size: 0.95rem;
}

.asset-stats {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 0.25rem;
}

.asset-amount {
    font-weight: 700;
    color: #10B981;
    font-size: 1rem;
}

.asset-percentage {
    font-size: 0.875rem;
    color: #64748B;
    font-weight: 600;
}

.portfolio-total {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1.25rem 1rem;
    background: linear-gradient(135deg, #EFF6FF 0%, #DBEAFE 100%);
    border-radius: 10px;
    border: 2px solid #2563EB;
    margin-top: 0.5rem;
}

.total-label {
    font-weight: 700;
    color: #1E3A8A;
    font-size: 1rem;
}

.total-amount {
    font-weight: 700;
    color: #2563EB;
    font-size: 1.5rem;
    font-family: 'Crimson Pro', serif;
}

.btn-rebalance {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    padding: 1rem;
    background: linear-gradient(135deg, #2563EB 0%, #1E3A8A 100%);
    color: white;
    border: none;
    border-radius: 10px;
    font-weight: 600;
    cursor: pointer;
    margin-top: 1rem;
    transition: all 0.2s ease;
}

.btn-rebalance:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(37, 99, 235, 0.3);
}

/* Modals */
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.6);
    backdrop-filter: blur(4px);
    display: none;
    align-items: center;
    justify-content: center;
    z-index: 9999;
}

.modal-overlay.active {
    display: flex;
}

.modal-content-large,
.modal-content-small {
    background: white;
    border-radius: 20px;
    width: 90%;
    max-width: 600px;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
}

.modal-content-small {
    max-width: 400px;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 2rem;
    border-bottom: 2px solid #F3F4F6;
}

.modal-header h3 {
    font-size: 1.5rem;
    font-weight: 700;
    color: #1E3A8A;
    font-family: 'Crimson Pro', serif;
    margin: 0;
}

.modal-close {
    width: 36px;
    height: 36px;
    background: #F3F4F6;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    color: #64748B;
    font-size: 1.5rem;
    transition: all 0.2s ease;
}

.modal-close:hover {
    background: #E5E7EB;
    color: #EF4444;
}

.modal-body {
    padding: 2rem;
}

.modal-footer {
    display: flex;
    gap: 1rem;
    padding: 2rem;
    border-top: 2px solid #F3F4F6;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-label {
    display: block;
    font-weight: 600;
    color: #1E3A8A;
    margin-bottom: 0.5rem;
    font-size: 0.95rem;
}

.form-input {
    width: 100%;
    padding: 0.75rem 1rem;
    border: 2px solid #E5E7EB;
    border-radius: 10px;
    font-size: 0.95rem;
    transition: all 0.2s ease;
}

.form-input:focus {
    outline: none;
    border-color: #2563EB;
    box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1);
}

.btn-secondary,
.btn-primary {
    flex: 1;
    padding: 0.875rem 1.5rem;
    border-radius: 10px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
    border: none;
    font-size: 1rem;
}

.btn-secondary {
    background: #F3F4F6;
    border: 2px solid #E5E7EB;
    color: #64748B;
}

.btn-secondary:hover {
    background: #E5E7EB;
}

.btn-primary {
    background: linear-gradient(135deg, #2563EB 0%, #1E3A8A 100%);
    color: white;
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(37, 99, 235, 0.4);
}

/* Photo Upload */
.photo-upload-area {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 1.5rem;
}

.photo-preview {
    width: 200px;
    height: 200px;
    border-radius: 50%;
    overflow: hidden;
    border: 4px solid #E5E7EB;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
}

.photo-preview img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.photo-placeholder {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 1rem;
    color: #9CA3AF;
}

.photo-placeholder p {
    font-size: 0.875rem;
    font-weight: 600;
}

.btn-upload {
    padding: 0.75rem 2rem;
    background: #2563EB;
    color: white;
    border: none;
    border-radius: 10px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
}

.btn-upload:hover {
    background: #1E3A8A;
}

/* Responsive */
@media (max-width: 1200px) {
    .portfolio-content {
        grid-template-columns: 1fr;
    }
    
    .chart-container {
        height: 300px;
    }
}

@media (max-width: 768px) {
    .dashboard-header-enhanced {
        flex-direction: column;
        padding: 1.5rem;
    }
    
    .profile-section {
        flex-direction: column;
        text-align: center;
        width: 100%;
    }
    
    .btn-edit-profile {
        width: 100%;
        justify-content: center;
    }
    
    .form-row {
        grid-template-columns: 1fr;
    }
    
    .chart-tabs {
        flex-direction: column;
    }
}
</style>
<div class="modern-dashboard">
    <div class="dashboard-header-enhanced">
        <div class="profile-section">
            <div class="profile-avatar-wrapper">
                @if(Auth::user()->profile_photo)
                    <img src="{{ asset('storage/' . Auth::user()->profile_photo) }}" alt="Profile" class="profile-avatar">
                @else
                    <div class="profile-avatar-placeholder">
                        {{ strtoupper(substr(Auth::user()->full_name ?? Auth::user()->name, 0, 2)) }}
                    </div>
                @endif
                <button class="avatar-edit-btn" onclick="openProfilePhotoModal()">
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                        <path d="M11.5 2L14 4.5L5 13.5H2.5V11L11.5 2Z" stroke="currentColor" stroke-width="2"/>
                    </svg>
                </button>
            </div>
            <div class="profile-info">
                <div class="profile-name-row">
                    <h1 class="profile-name">{{ Auth::user()->full_name ?? Auth::user()->name }}</h1>
                    <span class="status-indicator {{ Auth::user()->status === 'active' ? 'active' : 'inactive' }}">
                        <span class="status-dot"></span>
                        {{ ucfirst(Auth::user()->status ?? 'active') }}
                    </span>
                </div>
                <p class="profile-email">{{ Auth::user()->email }}</p>
                <p class="profile-date">Member since {{ Auth::user()->created_at->format('M d, Y') }}</p>
            </div>
            <button class="btn-edit-profile" onclick="openEditProfileModal()">
                <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                    <path d="M14 2L18 6L6 18H2V14L14 2Z" stroke="currentColor" stroke-width="2"/>
                </svg>
                Edit Profile
            </button>
        </div>
        <div class="header-actions">
            <button class="btn-icon" onclick="toggleNotifications()">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                    <path d="M18 8C18 6.4087 17.3679 4.88258 16.2426 3.75736C15.1174 2.63214 13.5913 2 12 2C10.4087 2 8.88258 2.63214 7.75736 3.75736C6.63214 4.88258 6 6.4087 6 8C6 15 3 17 3 17H21C21 17 18 15 18 8Z" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    <path d="M13.73 21C13.5542 21.3031 13.3019 21.5547 12.9982 21.7295C12.6946 21.9044 12.3504 21.9965 12 21.9965C11.6496 21.9965 11.3054 21.9044 11.0018 21.7295C10.6982 21.5547 10.4458 21.3031 10.27 21" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                </svg>
                @if(($notifications ?? collect())->where('read', false)->count() > 0)
                    <span class="notification-badge">{{ ($notifications ?? collect())->where('read', false)->count() }}</span>
                @endif
            </button>
        </div>
    </div>

    <!-- Investment Summary Cards (4 Cards) -->
    <div class="overview-grid">
        <!-- 1. Total Investment -->
        <div class="overview-card gradient-blue">
            <div class="card-icon-wrapper blue">
                <svg width="32" height="32" viewBox="0 0 32 32" fill="none">
                    <path d="M16 28C22.6274 28 28 22.6274 28 16C28 9.37258 22.6274 4 16 4C9.37258 4 4 9.37258 4 16C4 22.6274 9.37258 28 16 28Z" stroke="white" stroke-width="2"/>
                    <path d="M16 8V16L20 20" stroke="white" stroke-width="2" stroke-linecap="round"/>
                </svg>
            </div>
            <div class="card-content-white">
                <p class="card-label-white">Total Investment</p>
                <h2 class="card-amount-white">${{ number_format($totalInvested ?? 0, 2) }}</h2>
                <p class="card-hint-white">Lifetime contributions</p>
            </div>
        </div>

        <!-- 2. Investor Balance -->
        <div class="overview-card">
            <div class="card-icon-wrapper green">
                <svg width="32" height="32" viewBox="0 0 32 32" fill="none">
                    <rect x="6" y="10" width="20" height="16" rx="2" stroke="currentColor" stroke-width="2"/>
                    <path d="M10 10V8C10 6.4 11.4 5 13 5H19C20.6 5 22 6.4 22 8V10" stroke="currentColor" stroke-width="2"/>
                    <circle cx="16" cy="18" r="3" stroke="currentColor" stroke-width="2"/>
                </svg>
            </div>
            <div class="card-content">
                <p class="card-label">Investor Balance</p>
                <h2 class="card-amount">${{ number_format($user->balance ?? 0, 2) }}</h2>
                <p class="card-change positive">
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                        <path d="M8 12V4M8 4L4 8M8 4L12 8" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                    +12.5% this month
                </p>
            </div>
        </div>

        <!-- 3. Available Withdrawal -->
        <div class="overview-card">
            <div class="card-icon-wrapper purple">
                <svg width="32" height="32" viewBox="0 0 32 32" fill="none">
                    <path d="M8 20L16 12L24 20" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    <path d="M16 12V28" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                </svg>
            </div>
            <div class="card-content">
                <p class="card-label">Available Withdrawal</p>
                <h2 class="card-amount">${{ number_format($user->balance - ($user->locked_balance ?? 0), 2) }}</h2>
                <button class="quick-action-btn" onclick="openWithdrawModal()">
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                        <path d="M5 8L8 11L11 8" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                    Quick Withdraw
                </button>
            </div>
        </div>

        <!-- 4. Active Investment Plans -->
        <div class="overview-card">
            <div class="card-icon-wrapper orange">
                <svg width="32" height="32" viewBox="0 0 32 32" fill="none">
                    <path d="M6 16L14 8L18 12L26 4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    <path d="M20 4H26V10" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                </svg>
            </div>
            <div class="card-content">
                <p class="card-label">Active Investment Plans</p>
                <h2 class="card-amount">{{ $activePlans ?? 0 }}</h2>
                <a href="{{ route('investor.plans') }}" class="quick-action-link">View Plans →</a>
            </div>
        </div>
    </div>

    <!-- ROI Progress Bar Card (REDESIGNED - CLEAN & PROFESSIONAL) -->
   <!-- Portfolio Distribution Chart (Like Binance) -->
    <div class="portfolio-chart-card">
        <div class="chart-card-header">
            <div>
                <h3 class="chart-card-title">Investment Portfolio Distribution</h3>
                <p class="chart-card-subtitle">Track your diversified investments across sectors</p>
            </div>
            <div class="chart-tabs">
                <button class="chart-tab active" onclick="switchChartView('pie')">
                    <svg width="18" height="18" viewBox="0 0 18 18" fill="none">
                        <circle cx="9" cy="9" r="8" stroke="currentColor" stroke-width="2"/>
                        <path d="M9 1V9H17" stroke="currentColor" stroke-width="2"/>
                    </svg>
                    Pie
                </button>
                <button class="chart-tab" onclick="switchChartView('bar')">
                    <svg width="18" height="18" viewBox="0 0 18 18" fill="none">
                        <rect x="2" y="10" width="3" height="6" fill="currentColor"/>
                        <rect x="7" y="6" width="3" height="10" fill="currentColor"/>
                        <rect x="12" y="8" width="3" height="8" fill="currentColor"/>
                    </svg>
                    Bar
                </button>
            </div>
        </div>

        <div class="portfolio-content">
            <!-- Chart Canvas -->
            <div class="chart-container">
                <canvas id="portfolioChart" width="400" height="300"></canvas>
            </div>

            <!-- Portfolio Breakdown -->
            <div class="portfolio-breakdown">
                <h4 class="breakdown-title">Asset Allocation</h4>
                
                <div class="asset-item">
                    <div class="asset-info">
                        <div class="asset-color" style="background: #2563EB;"></div>
                        <span class="asset-name">Real Estate Pool</span>
                    </div>
                    <div class="asset-stats">
                        <span class="asset-amount">${{ number_format($portfolioData['real_estate'] ?? 0, 2) }}</span>
                        <span class="asset-percentage">{{ $totalInvested > 0 ? number_format((($portfolioData['real_estate'] ?? 0) / $totalInvested) * 100, 1) : 0 }}%</span>
                    </div>
                </div>

                <div class="asset-item">
                    <div class="asset-info">
                        <div class="asset-color" style="background: #10B981;"></div>
                        <span class="asset-name">Tech Startup</span>
                    </div>
                    <div class="asset-stats">
                        <span class="asset-amount">${{ number_format($portfolioData['tech_startup'] ?? 0, 2) }}</span>
                        <span class="asset-percentage">{{ $totalInvested > 0 ? number_format((($portfolioData['tech_startup'] ?? 0) / $totalInvested) * 100, 1) : 0 }}%</span>
                    </div>
                </div>

                <div class="asset-item">
                    <div class="asset-info">
                        <div class="asset-color" style="background: #F59E0B;"></div>
                        <span class="asset-name">Fixed Digital Asset</span>
                    </div>
                    <div class="asset-stats">
                        <span class="asset-amount">${{ number_format($portfolioData['digital_asset'] ?? 0, 2) }}</span>
                        <span class="asset-percentage">{{ $totalInvested > 0 ? number_format((($portfolioData['digital_asset'] ?? 0) / $totalInvested) * 100, 1) : 0 }}%</span>
                    </div>
                </div>

                <div class="asset-item">
                    <div class="asset-info">
                        <div class="asset-color" style="background: #8B5CF6;"></div>
                        <span class="asset-name">Cash Reserve</span>
                    </div>
                    <div class="asset-stats">
                        <span class="asset-amount">${{ number_format($portfolioData['cash_reserve'] ?? 0, 2) }}</span>
                        <span class="asset-percentage">{{ $totalInvested > 0 ? number_format((($portfolioData['cash_reserve'] ?? 0) / $totalInvested) * 100, 1) : 0 }}%</span>
                    </div>
                </div>

                <div class="portfolio-total">
                    <span class="total-label">Total Portfolio</span>
                    <span class="total-amount">${{ number_format($totalInvested ?? 0, 2) }}</span>
                </div>

                <button class="btn-rebalance">
                    <svg width="18" height="18" viewBox="0 0 18 18" fill="none">
                        <path d="M14 4L10 8L6 4" stroke="currentColor" stroke-width="2"/>
                        <path d="M4 14L8 10L12 14" stroke="currentColor" stroke-width="2"/>
                    </svg>
                    Rebalance Portfolio
                </button>
            </div>
        </div>
    </div>

    <!-- Investment Countdown Tracker -->
    <div class="dashboard-card-modern">
        <div class="card-header-modern">
            <h3>Active Investments</h3>
            <a href="{{ route('investor.plans') }}" class="view-all-link">Invest More →</a>
        </div>
        <div class="countdown-grid">
            @forelse($activeInvestments ?? [] as $investment)
                <div class="countdown-card">
                    <div class="countdown-header">
                        <h4>{{ $investment->plan_name }}</h4>
                        <span class="plan-badge">{{ $investment->tier }}</span>
                    </div>
                    <div class="countdown-amount">${{ number_format($investment->amount, 2) }}</div>
                    <div class="countdown-info">
                        <div class="info-item">
                            <span class="info-label">ROI</span>
                            <span class="info-value">{{ $investment->roi_percentage }}%</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Duration</span>
                            <span class="info-value">{{ $investment->duration_days }} days</span>
                        </div>
                    </div>
                    <div class="countdown-progress">
                        <div class="countdown-bar" style="width: {{ $investment->progress_percentage }}%"></div>
                    </div>
                    <div class="countdown-time">
                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                            <circle cx="8" cy="8" r="7" stroke="currentColor" stroke-width="2"/>
                            <path d="M8 4V8L11 10" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                        <span>{{ $investment->days_remaining }} days remaining</span>
                    </div>
                </div>
            @empty
                <div class="empty-state">
                    <svg width="64" height="64" viewBox="0 0 64 64" fill="none">
                        <circle cx="32" cy="32" r="30" fill="#F3F4F6"/>
                        <path d="M32 20V32L38 38" stroke="#9CA3AF" stroke-width="3" stroke-linecap="round"/>
                    </svg>
                    <p>No active investments</p>
                    <a href="{{ route('investor.plans') }}" class="btn-start-investing">Start Investing</a>
                </div>
            @endforelse
        </div>
    </div>
    <!-- Transaction History -->
    <div class="dashboard-card-modern">
        <div class="card-header-modern">
            <h3>Transaction History</h3>
            <div class="header-actions">
                <button class="btn-icon-small" onclick="downloadTransactions()">
                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                        <path d="M17 13V17H3V13" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        <path d="M10 3V13M10 13L6 9M10 13L14 9" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                    Download
                </button>
                <select class="filter-select">
                    <option>All Transactions</option>
                    <option>Deposits</option>
                    <option>Withdrawals</option>
                    <option>Profits</option>
                </select>
            </div>
        </div>
        <div class="table-container">
            <table class="modern-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Type</th>
                        <th>Plan</th>
                        <th>Amount</th>
                        <th>ROI Earned</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions ?? [] as $transaction)
                        <tr>
                            <td>
                                <div class="date-cell">
                                    <span class="date-main">{{ $transaction->created_at->format('M d, Y') }}</span>
                                    <span class="date-time">{{ $transaction->created_at->format('h:i A') }}</span>
                                </div>
                            </td>
                            <td>
                                <div class="type-cell">
                                    <span class="type-icon {{ $transaction->type }}">
                                        @if($transaction->type == 'deposit')
                                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                                <path d="M8 12V4M8 4L4 8M8 4L12 8" stroke="currentColor" stroke-width="2"/>
                                            </svg>
                                        @elseif($transaction->type == 'withdrawal')
                                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                                <path d="M8 4V12M8 12L4 8M8 12L12 8" stroke="currentColor" stroke-width="2"/>
                                            </svg>
                                        @else
                                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                                <circle cx="8" cy="8" r="6" stroke="currentColor" stroke-width="2"/>
                                            </svg>
                                        @endif
                                    </span>
                                    <span>{{ ucfirst($transaction->type) }}</span>
                                </div>
                            </td>
                            <td>{{ $transaction->plan_name ?? '-' }}</td>
                            <td>
                                <span class="amount {{ $transaction->type == 'deposit' ? 'positive' : 'negative' }}">
                                    {{ $transaction->type == 'withdrawal' ? '-' : '+' }}${{ number_format($transaction->amount, 2) }}
                                </span>
                            </td>
                            <td>
                                @if($transaction->roi_earned)
                                    <span class="roi-earned">+${{ number_format($transaction->roi_earned, 2) }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <span class="status-pill {{ $transaction->status }}">
                                    {{ ucfirst($transaction->status) }}
                                </span>
                            </td>
                            <td>
                                <button class="btn-action" onclick="downloadReceipt({{ $transaction->id }})">
                                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                        <path d="M14 10V14H2V10" stroke="currentColor" stroke-width="2"/>
                                        <path d="M8 2V10M8 10L5 7M8 10L11 7" stroke="currentColor" stroke-width="2"/>
                                    </svg>
                                    Receipt
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="empty-table">
                                <div class="empty-state-small">
                                    <p>No transactions yet</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if(($transactions ?? collect())->count() > 0)
            <div class="table-footer">
                <a href="{{ route('investor.transactions') }}" class="view-all-transactions">View All Transactions →</a>
            </div>
        @endif
    </div>

    <!-- Notification Panel (Hidden by default) -->
    <div class="notification-panel" id="notificationPanel">
        <div class="notification-header">
            <h3>Notifications</h3>
            <button class="close-btn" onclick="toggleNotifications()">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                    <path d="M18 6L6 18M6 6L18 18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                </svg>
            </button>
        </div>
        <div class="notification-list">
            @forelse($notifications ?? [] as $notification)
                <div class="notification-item {{ $notification->read ? 'read' : 'unread' }}">
                    <div class="notification-icon {{ $notification->type }}">
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                            <circle cx="10" cy="10" r="8" stroke="currentColor" stroke-width="2"/>
                            <path d="M10 6V11" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            <circle cx="10" cy="14" r="1" fill="currentColor"/>
                        </svg>
                    </div>
                    <div class="notification-content">
                        <p class="notification-title">{{ $notification->title }}</p>
                        <p class="notification-message">{{ $notification->message }}</p>
                        <span class="notification-time">{{ $notification->created_at->diffForHumans() }}</span>
                    </div>
                </div>
            @empty
                <div class="empty-notifications">
                    <p>No notifications</p>
                </div>
            @endforelse
        </div>
    </div>
</div>

@push('scripts')
<script>
// Toggle Notifications
function toggleNotifications() {
    const panel = document.getElementById('notificationPanel');
    panel.classList.toggle('active');
}

// Download Transactions
function downloadTransactions() {
    window.location.href = '{{ route("investor.transactions.download") }}';
}

// Download Receipt
function downloadReceipt(id) {
    window.location.href = `/investor/transactions/${id}/receipt`;
}

// Open Withdraw Modal
function openWithdrawModal() {
    alert('Withdraw modal - to be implemented');
}
</script>
@endpush
@endsection