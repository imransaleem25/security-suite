@php
    use ImranSaleem\SecuritySuite\Support\MenuHelper;
    $menu    = config('security_suite.menu', []);
    $items   = $menu['items'] ?? [];
    $canView = MenuHelper::canViewLogsMenu();
@endphp

@if(($menu['enabled'] ?? true) && $canView)
<div class="security-suite-logs-menu mb-3">
    <div class="list-group list-group-flush">
        <div class="list-group-item fw-semibold text-muted small text-uppercase border-0 px-3 py-2">
            <i class="{{ $menu['icon'] ?? 'bi-journal-text' }} me-1"></i>{{ $menu['label'] ?? 'Logs' }}
        </div>
        @if($items['audit'] ?? true)
        <a href="{{ route('audit.logs.index') }}"
           class="list-group-item list-group-item-action border-0 px-3{{ request()->routeIs('audit.logs.*') ? ' active' : '' }}">
            <i class="bi bi-shield-check me-2"></i>Audit Logs
        </a>
        @endif
        @if($items['password_history'] ?? true)
        <a href="{{ route('password.change.logs') }}"
           class="list-group-item list-group-item-action border-0 px-3{{ request()->routeIs('password.change.logs', 'users.password.history') ? ' active' : '' }}">
            <i class="bi bi-key me-2"></i>Password History
        </a>
        @endif
        @if($items['failed_login'] ?? true)
        <a href="{{ route('login.failed.logs') }}"
           class="list-group-item list-group-item-action border-0 px-3{{ request()->routeIs('login.failed.logs') ? ' active' : '' }}">
            <i class="bi bi-exclamation-triangle me-2"></i>Failed Login Logs
        </a>
        @endif
        @if($items['login_logout'] ?? true)
        <a href="{{ route('login.logs.index') }}"
           class="list-group-item list-group-item-action border-0 px-3{{ request()->routeIs('login.logs.index') ? ' active' : '' }}">
            <i class="bi bi-box-arrow-in-right me-2"></i>Login & Logout Logs
        </a>
        @endif
        @if($items['http'] ?? true)
        <a href="{{ route('http.logs.index') }}"
           class="list-group-item list-group-item-action border-0 px-3{{ request()->routeIs('http.logs.*') ? ' active' : '' }}">
            <i class="bi bi-globe me-2"></i>HTTP Logs
        </a>
        @endif
    </div>
</div>
@endif
