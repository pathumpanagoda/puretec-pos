@extends('layouts.app')

@section('title', 'Cash Balance Sheet')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('reports.index') }}">Reports</a></li>
    <li class="breadcrumb-item active">Cash Balance Sheet</li>
@endsection

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Cash Balance Sheet</h1>
        <p class="page-subtitle">{{ \Carbon\Carbon::parse($startDate)->format('M d, Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('M d, Y') }}</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('reports.index') }}" class="btn btn-outline-secondary btn-cpos me-2"><i class="bi bi-arrow-left me-2"></i>Back</a>
        @include('components.report-export', ['report' => 'balance-sheet', 'params' => ['from' => $startDate, 'to' => $endDate]])
    </div>
</div>

{{-- Date Filter --}}
<div class="cpos-card mb-4">
    <div class="card-body py-3">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label">From</label>
                <input type="date" name="from" class="form-control" value="{{ $startDate }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">To</label>
                <input type="date" name="to" class="form-control" value="{{ $endDate }}">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn-cpos btn-primary w-100">
                    <i class="bi bi-funnel"></i> Filter
                </button>
            </div>
            <div class="col-md-4 text-end">
                <div class="btn-group" role="group">
                    <a href="{{ route('reports.balance-sheet', ['from' => now()->startOfWeek()->toDateString(), 'to' => now()->toDateString()]) }}" class="btn btn-outline-secondary btn-sm">This Week</a>
                    <a href="{{ route('reports.balance-sheet', ['from' => now()->startOfMonth()->toDateString(), 'to' => now()->toDateString()]) }}" class="btn btn-outline-secondary btn-sm">This Month</a>
                    <a href="{{ route('reports.balance-sheet', ['from' => now()->startOfYear()->toDateString(), 'to' => now()->toDateString()]) }}" class="btn btn-outline-secondary btn-sm">This Year</a>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Balance Summary Cards --}}
<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="stat-card stat-blue">
            <div class="stat-icon"><i class="bi bi-wallet2"></i></div>
            <div class="stat-value">Rs. {{ number_format($openingBalance, 0) }}</div>
            <div class="stat-label">Opening Balance</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card stat-green">
            <div class="stat-icon"><i class="bi bi-arrow-down-circle"></i></div>
            <div class="stat-value">Rs. {{ number_format($totalInflows, 0) }}</div>
            <div class="stat-label">Total Inflows</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card stat-danger">
            <div class="stat-icon"><i class="bi bi-arrow-up-circle"></i></div>
            <div class="stat-value">Rs. {{ number_format($totalOutflows, 0) }}</div>
            <div class="stat-label">Total Outflows</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card {{ $closingBalance >= $openingBalance ? 'stat-green' : 'stat-warning' }}">
            <div class="stat-icon"><i class="bi bi-safe"></i></div>
            <div class="stat-value">Rs. {{ number_format($closingBalance, 0) }}</div>
            <div class="stat-label">Closing Balance</div>
        </div>
    </div>
</div>

{{-- Net Change Indicator --}}
<div class="cpos-card mb-4">
    <div class="card-body py-3">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h6 class="mb-0">Net Change for Period</h6>
                <small class="text-muted">{{ \Carbon\Carbon::parse($startDate)->format('M d') }} - {{ \Carbon\Carbon::parse($endDate)->format('M d, Y') }}</small>
            </div>
            <div class="col-md-6 text-end">
                <span class="fs-3 fw-bold {{ $netChange >= 0 ? 'text-success' : 'text-danger' }}">
                    {{ $netChange >= 0 ? '+' : '' }}Rs. {{ number_format($netChange, 2) }}
                </span>
                @if($netChange >= 0)
                    <i class="bi bi-arrow-up-circle-fill text-success ms-2"></i>
                @else
                    <i class="bi bi-arrow-down-circle-fill text-danger ms-2"></i>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    {{-- Inflows Breakdown --}}
    <div class="col-lg-6">
        <div class="cpos-card h-100">
            <div class="cpos-card-header">
                <h5 class="card-title"><i class="bi bi-arrow-down-circle text-success"></i> Inflows Breakdown</h5>
                <span class="fw-600 text-success">Rs. {{ number_format($totalInflows, 2) }}</span>
            </div>
            <div class="card-body p-4">
                @foreach($inflowBreakdown as $item)
                <div class="balance-item">
                    <div class="bi-icon bg-{{ $item['color'] }}-light"><i class="bi {{ $item['icon'] }}"></i></div>
                    <div class="bi-info">
                        <span class="bi-name">{{ $item['name'] }}</span>
                        <div class="progress mt-1" style="height: 4px;">
                            <div class="progress-bar bg-{{ $item['color'] }}" style="width: {{ $totalInflows > 0 ? ($item['amount'] / $totalInflows * 100) : 0 }}%"></div>
                        </div>
                    </div>
                    <span class="bi-amount text-success">Rs. {{ number_format($item['amount'], 2) }}</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Outflows Breakdown --}}
    <div class="col-lg-6">
        <div class="cpos-card h-100">
            <div class="cpos-card-header">
                <h5 class="card-title"><i class="bi bi-arrow-up-circle text-danger"></i> Outflows Breakdown</h5>
                <span class="fw-600 text-danger">Rs. {{ number_format($totalOutflows, 2) }}</span>
            </div>
            <div class="card-body p-4">
                @foreach($outflowBreakdown as $item)
                <div class="balance-item">
                    <div class="bi-icon bg-{{ $item['color'] }}-light"><i class="bi {{ $item['icon'] }}"></i></div>
                    <div class="bi-info">
                        <span class="bi-name">{{ $item['name'] }}</span>
                        <div class="progress mt-1" style="height: 4px;">
                            <div class="progress-bar bg-{{ $item['color'] }}" style="width: {{ $totalOutflows > 0 ? ($item['amount'] / $totalOutflows * 100) : 0 }}%"></div>
                        </div>
                    </div>
                    <span class="bi-amount text-danger">Rs. {{ number_format($item['amount'], 2) }}</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

{{-- Daily Balance Sheet Table --}}
<div class="cpos-card mt-4">
    <div class="cpos-card-header">
        <h5 class="card-title"><i class="bi bi-table"></i> Daily Balance Sheet</h5>
        <span class="badge bg-primary">{{ count($dailyTransactions) }} days with transactions</span>
    </div>
    <div class="table-responsive">
        <table class="table table-cpos mb-0">
            <thead>
                <tr>
                    <th>Date</th>
                    <th class="text-end text-success">Cash In</th>
                    <th class="text-end text-primary">Card In</th>
                    <th class="text-end text-info">Other In</th>
                    <th class="text-end fw-bold" style="background: var(--cp-success-light);">Total In</th>
                    <th class="text-end text-danger">Cash Out</th>
                    <th class="text-end text-warning">Bank Out</th>
                    <th class="text-end" style="color: #7c3aed;">Purchases</th>
                    <th class="text-end fw-bold" style="background: var(--cp-danger-light);">Total Out</th>
                    <th class="text-end fw-bold">Net</th>
                    <th class="text-end fw-bold" style="background: var(--cp-blue-100);">Balance</th>
                </tr>
            </thead>
            <tbody>
                {{-- Opening Balance Row --}}
                <tr style="background: var(--cp-bg-alt);">
                    <td class="fw-bold">Opening Balance</td>
                    <td colspan="9"></td>
                    <td class="text-end fw-bold">Rs. {{ number_format($openingBalance, 2) }}</td>
                </tr>

                @forelse($dailyTransactions as $day)
                <tr>
                    <td class="fw-600">{{ \Carbon\Carbon::parse($day['date'])->format('M d (D)') }}</td>
                    <td class="text-end text-success">
                        @if($day['cash_sales'] > 0)
                            Rs. {{ number_format($day['cash_sales'], 2) }}
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>
                    <td class="text-end text-primary">
                        @if($day['card_sales'] > 0)
                            Rs. {{ number_format($day['card_sales'], 2) }}
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>
                    <td class="text-end text-info">
                        @if($day['other_sales'] > 0)
                            Rs. {{ number_format($day['other_sales'], 2) }}
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>
                    <td class="text-end fw-600 text-success" style="background: var(--cp-success-light);">
                        Rs. {{ number_format($day['total_in'], 2) }}
                    </td>
                    <td class="text-end text-danger">
                        @if($day['cash_expenses'] > 0)
                            Rs. {{ number_format($day['cash_expenses'], 2) }}
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>
                    <td class="text-end text-warning">
                        @if($day['bank_expenses'] > 0)
                            Rs. {{ number_format($day['bank_expenses'], 2) }}
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>
                    <td class="text-end" style="color: #7c3aed;">
                        @if($day['purchases'] > 0)
                            Rs. {{ number_format($day['purchases'], 2) }}
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>
                    <td class="text-end fw-600 text-danger" style="background: var(--cp-danger-light);">
                        Rs. {{ number_format($day['total_out'], 2) }}
                    </td>
                    <td class="text-end fw-bold {{ $day['net'] >= 0 ? 'text-success' : 'text-danger' }}">
                        {{ $day['net'] >= 0 ? '+' : '' }}Rs. {{ number_format($day['net'], 2) }}
                    </td>
                    <td class="text-end fw-bold" style="background: var(--cp-blue-100);">
                        Rs. {{ number_format($day['balance'], 2) }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="11" class="text-center py-4 text-muted">
                        <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                        No transactions found for this period
                    </td>
                </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr class="fw-bold" style="background: var(--cp-bg-alt);">
                    <td>TOTALS</td>
                    <td class="text-end text-success">Rs. {{ number_format($totalCashIn, 2) }}</td>
                    <td class="text-end text-primary">Rs. {{ number_format($totalCardIn, 2) }}</td>
                    <td class="text-end text-info">Rs. {{ number_format($totalOtherIn, 2) }}</td>
                    <td class="text-end text-success" style="background: var(--cp-success-light);">Rs. {{ number_format($totalInflows, 2) }}</td>
                    <td class="text-end text-danger">Rs. {{ number_format($totalCashOut, 2) }}</td>
                    <td class="text-end text-warning">Rs. {{ number_format($totalBankOut, 2) }}</td>
                    <td class="text-end" style="color: #7c3aed;">Rs. {{ number_format($totalPurchaseOut, 2) }}</td>
                    <td class="text-end text-danger" style="background: var(--cp-danger-light);">Rs. {{ number_format($totalOutflows, 2) }}</td>
                    <td class="text-end {{ $netChange >= 0 ? 'text-success' : 'text-danger' }}">
                        {{ $netChange >= 0 ? '+' : '' }}Rs. {{ number_format($netChange, 2) }}
                    </td>
                    <td class="text-end" style="background: var(--cp-blue-100);">Rs. {{ number_format($closingBalance, 2) }}</td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

{{-- Cash Reconciliation --}}
@if($recentSessions->count() > 0)
<div class="cpos-card mt-4">
    <div class="cpos-card-header">
        <h5 class="card-title"><i class="bi bi-clipboard-check"></i> Cash Reconciliation</h5>
        @if($totalDiscrepancy != 0)
            <span class="badge {{ $totalDiscrepancy < 0 ? 'bg-danger' : 'bg-success' }}">
                {{ $totalDiscrepancy < 0 ? 'Shortage' : 'Overage' }}: Rs. {{ number_format(abs($totalDiscrepancy), 2) }}
            </span>
        @else
            <span class="badge bg-success">Balanced</span>
        @endif
    </div>
    <div class="card-body">
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="p-3 rounded" style="background: var(--cp-bg-alt);">
                    <small class="text-muted d-block">Expected Cash</small>
                    <span class="fs-5 fw-bold">Rs. {{ number_format($totalExpected, 2) }}</span>
                </div>
            </div>
            <div class="col-md-4">
                <div class="p-3 rounded" style="background: var(--cp-bg-alt);">
                    <small class="text-muted d-block">Actual Cash Counted</small>
                    <span class="fs-5 fw-bold">Rs. {{ number_format($totalActual, 2) }}</span>
                </div>
            </div>
            <div class="col-md-4">
                <div class="p-3 rounded {{ $totalDiscrepancy < 0 ? 'bg-danger-soft' : ($totalDiscrepancy > 0 ? 'bg-success-soft' : '') }}" style="background: var(--cp-bg-alt);">
                    <small class="text-muted d-block">Discrepancy</small>
                    <span class="fs-5 fw-bold {{ $totalDiscrepancy < 0 ? 'text-danger' : ($totalDiscrepancy > 0 ? 'text-success' : '') }}">
                        {{ $totalDiscrepancy >= 0 ? '+' : '' }}Rs. {{ number_format($totalDiscrepancy, 2) }}
                    </span>
                </div>
            </div>
        </div>

        <h6 class="mb-3">Recent Register Sessions</h6>
        <div class="table-responsive">
            <table class="table table-sm table-cpos mb-0">
                <thead>
                    <tr>
                        <th>Cashier</th>
                        <th>Opened</th>
                        <th>Closed</th>
                        <th class="text-end">Expected</th>
                        <th class="text-end">Actual</th>
                        <th class="text-end">Difference</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recentSessions as $session)
                    @php
                        $diff = ($session->closing_balance ?? 0) - ($session->expected_balance ?? 0);
                    @endphp
                    <tr>
                        <td>{{ $session->user?->name ?? 'Unknown' }}</td>
                        <td>{{ $session->opened_at?->format('M d, H:i') }}</td>
                        <td>{{ $session->closed_at?->format('M d, H:i') }}</td>
                        <td class="text-end">Rs. {{ number_format($session->expected_balance ?? 0, 2) }}</td>
                        <td class="text-end">Rs. {{ number_format($session->closing_balance ?? 0, 2) }}</td>
                        <td class="text-end fw-bold {{ $diff < 0 ? 'text-danger' : ($diff > 0 ? 'text-success' : '') }}">
                            @if($diff != 0)
                                {{ $diff >= 0 ? '+' : '' }}Rs. {{ number_format($diff, 2) }}
                                @if($diff < 0)
                                    <i class="bi bi-exclamation-triangle-fill text-danger ms-1"></i>
                                @endif
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif

@push('styles')
<style>
.balance-item {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 16px 0;
    border-bottom: 1px solid var(--cp-border-light);
}
.balance-item:last-child { border-bottom: none; }
.bi-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
}
.bg-success-light { background: var(--cp-success-light); color: var(--cp-success); }
.bg-primary-light { background: var(--cp-blue-100); color: var(--cp-primary); }
.bg-info-light { background: #e0f2fe; color: #0284c7; }
.bg-danger-light { background: var(--cp-danger-light); color: var(--cp-danger); }
.bg-warning-light { background: var(--cp-warning-light); color: var(--cp-warning); }
.bg-purple-light { background: #ede9fe; color: #7c3aed; }
.bi-info { flex: 1; }
.bi-name { display: block; font-weight: 600; font-size: 14px; }
.bi-amount { font-weight: 700; font-size: 15px; white-space: nowrap; }
.stat-danger::before { background: var(--cp-danger); }
.stat-danger .stat-icon { color: var(--cp-danger); background: var(--cp-danger-light); }
.bg-success-soft { background: var(--cp-success-light) !important; }
.bg-danger-soft { background: var(--cp-danger-light) !important; }
</style>
@endpush
@endsection
