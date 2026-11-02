@extends('puretec.layouts.app')

@section('title', 'Features — ' . $tenant->business_name)

@section('content')
<div class="page-header d-flex justify-content-between align-items-start">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('puretec.tenants.index') }}">Tenants</a></li>
                <li class="breadcrumb-item"><a href="{{ route('puretec.tenants.show', $tenant) }}">{{ $tenant->code }}</a></li>
                <li class="breadcrumb-item active">Features</li>
            </ol>
        </nav>
        <h1 class="page-title">Feature Management</h1>
        <p class="text-muted">
            Enable or disable features for <strong>{{ $tenant->business_name }}</strong>
            <code>({{ $tenant->code }})</code>
        </p>
    </div>
    <a href="{{ route('puretec.tenants.show', $tenant) }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Back to Tenant
    </a>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        <i class="bi bi-check-circle me-1"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<form action="{{ route('puretec.tenants.features.update', $tenant) }}" method="POST">
    @csrf

    @php
        $groupLabels = [
            'core' => ['Core Features', 'These features cannot be disabled. They are essential for POS operation.', 'bi-shield-check'],
            'standard' => ['Standard Features', 'Common features included in most subscription plans.', 'bi-grid-3x3-gap'],
            'advanced' => ['Advanced Features', 'Optional advanced features that can be enabled per client.', 'bi-rocket-takeoff'],
            'addon' => ['Add-on Features', 'Specialized modules for specific business types.', 'bi-puzzle'],
            'platform' => ['Platform Features', 'Cloud and connectivity features.', 'bi-cloud'],
        ];
    @endphp

    @foreach($definitions as $group => $features)
        @php
            $label = $groupLabels[$group] ?? [ucfirst($group), '', 'bi-star'];
        @endphp
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi {{ $label[2] }} me-2"></i>
                    {{ $label[0] }}
                </h5>
                @if($label[1])
                    <small class="text-muted">{{ $label[1] }}</small>
                @endif
            </div>
            <div class="card-body">
                <div class="row g-3">
                    @foreach($features as $feature)
                        <div class="col-md-4 col-lg-3">
                            <div class="form-check feature-card p-3 rounded border {{ ($featureMap[$feature['code']] ?? false) ? 'border-success bg-success bg-opacity-10' : 'border-secondary bg-light' }}">
                                <input
                                    class="form-check-input"
                                    type="checkbox"
                                    name="features[]"
                                    value="{{ $feature['code'] }}"
                                    id="feature_{{ $feature['code'] }}"
                                    {{ ($featureMap[$feature['code']] ?? false) ? 'checked' : '' }}
                                    {{ $feature['is_core'] ? 'checked disabled' : '' }}
                                >
                                @if($feature['is_core'])
                                    {{-- Hidden input to ensure core features are always submitted --}}
                                    <input type="hidden" name="features[]" value="{{ $feature['code'] }}">
                                @endif
                                <label class="form-check-label d-block" for="feature_{{ $feature['code'] }}">
                                    <i class="bi {{ $feature['icon'] ?? 'bi-star' }} me-1"></i>
                                    <strong>{{ $feature['name'] }}</strong>
                                    @if($feature['is_core'])
                                        <span class="badge bg-primary ms-1" style="font-size:0.65rem">Core</span>
                                    @endif
                                </label>
                                @if($feature['description'])
                                    <small class="text-muted d-block mt-1">{{ $feature['description'] }}</small>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endforeach

    <div class="d-flex justify-content-between">
        <a href="{{ route('puretec.tenants.show', $tenant) }}" class="btn btn-secondary">
            <i class="bi bi-x me-1"></i> Cancel
        </a>
        <button type="submit" class="btn btn-primary">
            <i class="bi bi-check-circle me-1"></i> Save Feature Configuration
        </button>
    </div>
</form>

@push('styles')
<style>
    .feature-card {
        transition: all 0.2s ease;
        cursor: pointer;
    }
    .feature-card:hover {
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        transform: translateY(-1px);
    }
    .feature-card .form-check-input:checked + .form-check-label {
        color: #198754;
    }
</style>
@endpush
@endsection
