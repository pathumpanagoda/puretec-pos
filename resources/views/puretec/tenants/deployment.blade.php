@extends('puretec.layouts.app')

@section('title', 'Deployment Config - ' . $tenant->business_name)

@section('content')
<div class="page-header">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('puretec.tenants.index') }}">Tenants</a></li>
            <li class="breadcrumb-item"><a href="{{ route('puretec.tenants.show', $tenant) }}">{{ $tenant->business_name }}</a></li>
            <li class="breadcrumb-item active">Deployment</li>
        </ol>
    </nav>
    <h1 class="page-title">Deployment Config</h1>
    <p class="text-muted">Server and environment configuration for <strong>{{ $tenant->business_name }}</strong></p>
</div>

<!-- Deployment Overview -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i>Deployment Overview</h5>
    </div>
    <div class="card-body">
        <dl class="row mb-0">
            <dt class="col-sm-3">Subdomain</dt>
            <dd class="col-sm-9"><code>{{ $deployment['subdomain'] }}</code></dd>

            <dt class="col-sm-3">Database Name</dt>
            <dd class="col-sm-9"><code>{{ $deployment['database_name'] }}</code></dd>

            <dt class="col-sm-3">App URL</dt>
            <dd class="col-sm-9">
                <a href="{{ $deployment['app_url'] }}" target="_blank" rel="noopener">
                    {{ $deployment['app_url'] }} <i class="bi bi-box-arrow-up-right ms-1"></i>
                </a>
            </dd>

            <dt class="col-sm-3">API URL</dt>
            <dd class="col-sm-9">
                <a href="{{ $deployment['api_url'] }}" target="_blank" rel="noopener">
                    {{ $deployment['api_url'] }} <i class="bi bi-box-arrow-up-right ms-1"></i>
                </a>
            </dd>
        </dl>
    </div>
</div>

<!-- Automated Bash Provisioning Script -->
@if(!empty($deployment['provisioning_script']))
<div class="card mb-4 border-primary">
    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="bi bi-terminal me-2"></i>Automated VPS Provisioning Script</h5>
        <button type="button" class="btn btn-sm btn-light btn-copy"
                onclick="copyToClipboard('provisioning-script', this)" title="Copy to clipboard">
            <i class="bi bi-clipboard me-1"></i>Copy Bash Script
        </button>
    </div>
    <div class="card-body">
        <p class="text-muted small mb-2">
            Run this script on your Ubuntu/Debian VPS as <code>root</code> to automatically create the MySQL database, clone/rsync codebase, deploy Nginx config, run migrations, and obtain Let's Encrypt SSL.
        </p>
        <div class="bg-dark rounded p-3">
            <pre class="text-light mb-0" style="max-height: 350px; overflow-y: auto;" id="provisioning-script"><code>{{ $deployment['provisioning_script'] }}</code></pre>
        </div>
    </div>
</div>
@endif

<!-- Manual Setup Steps -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0"><i class="bi bi-list-check me-2"></i>Manual Setup Steps</h5>
    </div>
    <div class="card-body">
        @if(!empty($deployment['manual_steps']))
            <ol class="mb-0">
                @foreach($deployment['manual_steps'] as $step)
                    <li class="mb-2">{{ $step }}</li>
                @endforeach
            </ol>
        @else
            <p class="text-muted mb-0">No manual steps defined.</p>
        @endif
    </div>
</div>

<!-- Nginx Configuration -->
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="bi bi-hdd-rack me-2"></i>Nginx Configuration</h5>
        <button type="button" class="btn btn-sm btn-outline-secondary btn-copy"
                onclick="copyToClipboard('nginx-config', this)" title="Copy to clipboard">
            <i class="bi bi-clipboard me-1"></i>Copy
        </button>
    </div>
    <div class="card-body bg-dark rounded-bottom">
        <pre class="text-light mb-0" id="nginx-config"><code>{{ $deployment['nginx_config'] }}</code></pre>
    </div>
</div>

<!-- .env Template -->
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="bi bi-file-earmark-code me-2"></i>.env Template</h5>
        <button type="button" class="btn btn-sm btn-outline-secondary btn-copy"
                onclick="copyToClipboard('env-template', this)" title="Copy to clipboard">
            <i class="bi bi-clipboard me-1"></i>Copy
        </button>
    </div>
    <div class="card-body bg-dark rounded-bottom">
        <pre class="text-light mb-0" id="env-template"><code>{{ $deployment['env_template'] }}</code></pre>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function copyToClipboard(elementId, button) {
        var text = document.getElementById(elementId).innerText;
        navigator.clipboard.writeText(text).then(function() {
            var originalHtml = button.innerHTML;
            button.innerHTML = '<i class="bi bi-check me-1"></i>Copied!';
            button.classList.remove('btn-outline-secondary');
            button.classList.add('btn-success');
            setTimeout(function() {
                button.innerHTML = originalHtml;
                button.classList.remove('btn-success');
                button.classList.add('btn-outline-secondary');
            }, 2000);
        }).catch(function() {
            // Fallback for older browsers
            var textarea = document.createElement('textarea');
            textarea.value = text;
            textarea.style.position = 'fixed';
            textarea.style.opacity = '0';
            document.body.appendChild(textarea);
            textarea.select();
            document.execCommand('copy');
            document.body.removeChild(textarea);

            var originalHtml = button.innerHTML;
            button.innerHTML = '<i class="bi bi-check me-1"></i>Copied!';
            button.classList.remove('btn-outline-secondary');
            button.classList.add('btn-success');
            setTimeout(function() {
                button.innerHTML = originalHtml;
                button.classList.remove('btn-success');
                button.classList.add('btn-outline-secondary');
            }, 2000);
        });
    }
</script>
@endpush
