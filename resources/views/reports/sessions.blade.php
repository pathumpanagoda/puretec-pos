@extends('layouts.app')

@section('title', 'Cashier Sessions Report')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('reports.index') }}">Reports</a></li>
    <li class="breadcrumb-item active">Cashier Sessions</li>
@endsection

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Cashier Sessions Report</h1>
        <p class="page-subtitle">{{ \Carbon\Carbon::parse($startDate)->format('M d, Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('M d, Y') }}</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('reports.index') }}" class="btn btn-outline-secondary btn-cpos me-2"><i class="bi bi-arrow-left me-2"></i>Back</a>
        @include('components.report-export', ['report' => 'sessions', 'params' => ['from' => $startDate, 'to' => $endDate]])
    </div>
</div>

{{-- Summary Stats --}}
<div class="row g-4 mb-4">
    <div class="col-md-2">
        <div class="stat-card stat-blue">
            <div class="stat-icon"><i class="bi bi-clock-history"></i></div>
            <div class="stat-value">{{ number_format($summary['total_sessions']) }}</div>
            <div class="stat-label">Total Sessions</div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="stat-card stat-green">
            <div class="stat-icon"><i class="bi bi-play-circle"></i></div>
            <div class="stat-value">{{ number_format($summary['open_sessions']) }}</div>
            <div class="stat-label">Active Now</div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="stat-card stat-blue">
            <div class="stat-icon"><i class="bi bi-cash-stack"></i></div>
            <div class="stat-value">Rs. {{ number_format($summary['total_sales'], 0) }}</div>
            <div class="stat-label">Total Sales</div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="stat-card stat-green">
            <div class="stat-icon"><i class="bi bi-currency-dollar"></i></div>
            <div class="stat-value">Rs. {{ number_format($summary['total_cash'], 0) }}</div>
            <div class="stat-label">Cash Collected</div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="stat-card {{ $summary['total_difference'] < 0 ? 'stat-danger' : 'stat-green' }}">
            <div class="stat-icon"><i class="bi bi-plus-slash-minus"></i></div>
            <div class="stat-value">Rs. {{ number_format($summary['total_difference'], 0) }}</div>
            <div class="stat-label">Cash Difference</div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="stat-card {{ $summary['sessions_with_shortage'] > 0 ? 'stat-warning' : 'stat-green' }}">
            <div class="stat-icon"><i class="bi bi-exclamation-triangle"></i></div>
            <div class="stat-value">{{ $summary['sessions_with_shortage'] }}</div>
            <div class="stat-label">Shortage Sessions</div>
        </div>
    </div>
</div>

{{-- Filters --}}
<div class="cpos-card mb-4">
    <div class="card-body py-3">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-2">
                <label class="form-label">From Date</label>
                <input type="date" name="from" class="form-control" value="{{ $startDate }}">
            </div>
            <div class="col-md-2">
                <label class="form-label">To Date</label>
                <input type="date" name="to" class="form-control" value="{{ $endDate }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">Cashier</label>
                <select name="user_id" class="form-select">
                    <option value="">All Cashiers</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}" {{ $userId == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">All Status</option>
                    <option value="open" {{ $status == 'open' ? 'selected' : '' }}>Open</option>
                    <option value="closed" {{ $status == 'closed' ? 'selected' : '' }}>Closed</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn-cpos btn-primary w-100"><i class="bi bi-funnel me-1"></i> Filter</button>
            </div>
            <div class="col-md-1">
                <a href="{{ route('reports.sessions') }}" class="btn btn-outline-secondary w-100">Reset</a>
            </div>
        </form>
    </div>
</div>

{{-- Sessions Table --}}
<div class="cpos-card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Session</th>
                        <th>Cashier</th>
                        <th>Opened</th>
                        <th>Closed</th>
                        <th>Duration</th>
                        <th class="text-end">Opening</th>
                        <th class="text-end">Sales</th>
                        <th class="text-end">Cash In</th>
                        <th class="text-end">Expected</th>
                        <th class="text-end">Actual</th>
                        <th class="text-end">Difference</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sessions as $session)
                    @php
                        $difference = ($session->closing_balance ?? 0) - ($session->expected_balance ?? 0);
                        $diffClass = '';
                        if ($session->status === 'closed') {
                            $diffClass = $difference < -1 ? 'text-danger fw-bold' : ($difference > 1 ? 'text-success' : '');
                        }
                    @endphp
                    <tr>
                        <td>
                            <div class="fw-600">#{{ $session->id }}</div>
                            <small class="text-muted">{{ $session->name }}</small>
                        </td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="avatar-sm bg-primary-soft text-primary">
                                    {{ strtoupper(substr($session->user?->name ?? 'U', 0, 1)) }}
                                </div>
                                <div>
                                    <div class="fw-600">{{ $session->user?->name ?? 'Unknown' }}</div>
                                    <small class="text-muted">{{ $session->user?->email ?? '' }}</small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div>{{ $session->opened_at?->format('M d, Y') }}</div>
                            <small class="text-muted">{{ $session->opened_at?->format('h:i A') }}</small>
                        </td>
                        <td>
                            @if($session->closed_at)
                                <div>{{ $session->closed_at->format('M d, Y') }}</div>
                                <small class="text-muted">{{ $session->closed_at->format('h:i A') }}</small>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            @if($session->status === 'closed' && $session->opened_at && $session->closed_at)
                                {{ $session->opened_at->diffForHumans($session->closed_at, true) }}
                            @elseif($session->status === 'open' && $session->opened_at)
                                <span class="text-success">{{ $session->opened_at->diffForHumans(now(), true) }}</span>
                            @else
                                -
                            @endif
                        </td>
                        <td class="text-end">Rs. {{ number_format($session->opening_balance, 2) }}</td>
                        <td class="text-end fw-600">Rs. {{ number_format($session->total_sales ?? 0, 2) }}</td>
                        <td class="text-end">Rs. {{ number_format($session->cash_in ?? 0, 2) }}</td>
                        <td class="text-end">Rs. {{ number_format($session->expected_balance ?? 0, 2) }}</td>
                        <td class="text-end">
                            @if($session->status === 'closed')
                                Rs. {{ number_format($session->closing_balance ?? 0, 2) }}
                            @else
                                -
                            @endif
                        </td>
                        <td class="text-end {{ $diffClass }}">
                            @if($session->status === 'closed')
                                {{ $difference >= 0 ? '+' : '' }}Rs. {{ number_format($difference, 2) }}
                                @if($difference < -1)
                                    <i class="bi bi-exclamation-circle-fill text-danger ms-1" title="Cash shortage"></i>
                                @endif
                            @else
                                -
                            @endif
                        </td>
                        <td>
                            @if($session->status === 'open')
                                <span class="badge bg-success-subtle text-success"><i class="bi bi-circle-fill me-1" style="font-size:8px"></i>Active</span>
                            @else
                                <span class="badge bg-secondary-subtle text-secondary">Closed</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="12" class="text-center py-5 text-muted">
                            <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                            No sessions found for this period
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($sessions->hasPages())
    <div class="card-footer">
        {{ $sessions->links() }}
    </div>
    @endif
</div>

@push('styles')
<style>
.avatar-sm {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 14px;
}
.bg-primary-soft {
    background: var(--cp-blue-100);
}
.stat-danger::before { background: var(--cp-danger); }
.stat-danger .stat-icon { color: var(--cp-danger); background: var(--cp-danger-light); }
.stat-warning::before { background: var(--cp-warning); }
.stat-warning .stat-icon { color: var(--cp-warning); background: var(--cp-warning-light); }
</style>
@endpush
@endsection
