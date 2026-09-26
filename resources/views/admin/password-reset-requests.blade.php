@extends('layouts.app')

@section('title', 'Password Reset Requests')

@section('breadcrumb')
<li class="breadcrumb-item active">Password Reset Requests</li>
@endsection

@section('content')
<div class="page-header d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="page-title">
            <i class="bi bi-key me-2"></i>Password Reset Requests
        </h1>
        <p class="text-muted mb-0">Review and manage password reset requests from users</p>
    </div>
    @if($pendingCount > 0)
    <span class="badge bg-warning text-dark fs-6">
        <i class="bi bi-clock me-1"></i>{{ $pendingCount }} Pending
    </span>
    @endif
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
        @if(session('reset_url'))
            <div class="mt-2 p-2 bg-dark rounded">
                <small class="text-muted d-block mb-1">Reset URL (share with <strong>{{ session('approved_user', 'the user') }}</strong>):</small>
                <code class="text-success" style="word-break: break-all; font-size: 0.75rem;">{{ session('reset_url') }}</code>
                <button type="button" class="btn btn-sm btn-outline-success ms-2" onclick="copyResetUrl(this)" data-url="{{ session('reset_url') }}">
                    <i class="bi bi-clipboard"></i> Copy
                </button>
            </div>
        @endif
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-circle me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

{{-- Filter Tabs --}}
<div class="card mb-4">
    <div class="card-body py-2">
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('password-reset-requests.index') }}"
               class="btn btn-sm {{ !request('status') ? 'btn-primary' : 'btn-outline-secondary' }}">
                All
            </a>
            <a href="{{ route('password-reset-requests.index', ['status' => 'pending']) }}"
               class="btn btn-sm {{ request('status') == 'pending' ? 'btn-warning' : 'btn-outline-secondary' }}">
                <i class="bi bi-clock me-1"></i>Pending
                @if($pendingCount > 0)
                    <span class="badge bg-danger ms-1">{{ $pendingCount }}</span>
                @endif
            </a>
            <a href="{{ route('password-reset-requests.index', ['status' => 'approved']) }}"
               class="btn btn-sm {{ request('status') == 'approved' ? 'btn-success' : 'btn-outline-secondary' }}">
                <i class="bi bi-check-circle me-1"></i>Approved
            </a>
            <a href="{{ route('password-reset-requests.index', ['status' => 'rejected']) }}"
               class="btn btn-sm {{ request('status') == 'rejected' ? 'btn-danger' : 'btn-outline-secondary' }}">
                <i class="bi bi-x-circle me-1"></i>Rejected
            </a>
            <a href="{{ route('password-reset-requests.index', ['status' => 'completed']) }}"
               class="btn btn-sm {{ request('status') == 'completed' ? 'btn-info' : 'btn-outline-secondary' }}">
                <i class="bi bi-check-all me-1"></i>Completed
            </a>
        </div>
    </div>
</div>

{{-- Requests Table --}}
<div class="card">
    <div class="card-body p-0">
        @if($requests->count() > 0)
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>User</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Reason</th>
                        <th>Status</th>
                        <th>Requested</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($requests as $req)
                    <tr>
                        <td>{{ $req->id }}</td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="avatar-sm bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center" style="width:32px;height:32px;">
                                    <span class="text-primary fw-bold" style="font-size:0.75rem;">{{ strtoupper(substr($req->user->name ?? '?', 0, 1)) }}</span>
                                </div>
                                <span class="fw-medium">{{ $req->user->name ?? 'Unknown' }}</span>
                            </div>
                        </td>
                        <td><small class="text-muted">{{ $req->user->email ?? '-' }}</small></td>
                        <td>
                            <span class="badge bg-secondary bg-opacity-25 text-light">
                                {{ ucfirst(str_replace('_', ' ', $req->user->role ?? '-')) }}
                            </span>
                        </td>
                        <td>
                            @if($req->reason)
                                <small class="text-muted" title="{{ $req->reason }}">
                                    {{ Str::limit($req->reason, 30) }}
                                </small>
                            @else
                                <small class="text-muted">—</small>
                            @endif
                        </td>
                        <td>
                            @switch($req->status)
                                @case('pending')
                                    <span class="badge bg-warning text-dark"><i class="bi bi-clock me-1"></i>Pending</span>
                                    @break
                                @case('approved')
                                    <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Approved</span>
                                    @if($req->isExpired())
                                        <span class="badge bg-secondary ms-1">Expired</span>
                                    @endif
                                    @break
                                @case('rejected')
                                    <span class="badge bg-danger"><i class="bi bi-x-circle me-1"></i>Rejected</span>
                                    @break
                                @case('completed')
                                    <span class="badge bg-info"><i class="bi bi-check-all me-1"></i>Completed</span>
                                    @break
                            @endswitch
                        </td>
                        <td>
                            <small class="text-muted">{{ $req->created_at->diffForHumans() }}</small>
                        </td>
                        <td>
                            @if($req->isPending())
                                <div class="d-flex gap-1">
                                    <form action="{{ route('password-reset-requests.approve', $req->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success" title="Approve" onclick="return confirm('Approve password reset for {{ $req->user->name }}?')">
                                            <i class="bi bi-check-lg"></i>
                                        </button>
                                    </form>
                                    <form action="{{ route('password-reset-requests.reject', $req->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-danger" title="Reject" onclick="return confirm('Reject password reset for {{ $req->user->name }}?')">
                                            <i class="bi bi-x-lg"></i>
                                        </button>
                                    </form>
                                </div>
                            @elseif($req->isApproved() && !$req->isExpired())
                                <small class="text-success">
                                    <i class="bi bi-clock me-1"></i>Expires {{ $req->expires_at->diffForHumans() }}
                                </small>
                            @else
                                <small class="text-muted">—</small>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="card-footer">
            {{ $requests->withQueryString()->links() }}
        </div>
        @else
        <div class="text-center py-5">
            <i class="bi bi-inbox display-4 text-muted"></i>
            <p class="text-muted mt-3">No password reset requests found.</p>
        </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
function copyResetUrl(btn) {
    const url = btn.dataset.url;
    navigator.clipboard.writeText(url).then(() => {
        btn.innerHTML = '<i class="bi bi-check"></i> Copied!';
        setTimeout(() => {
            btn.innerHTML = '<i class="bi bi-clipboard"></i> Copy';
        }, 2000);
    });
}
</script>
@endpush
