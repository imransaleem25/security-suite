@php
    use ImranSaleem\SecuritySuite\Support\MenuHelper;
    $menu    = config('security_suite.menu', []);
    $items   = $menu['items'] ?? [];
    $canView = MenuHelper::canViewLogsMenu();
    $isActive = request()->routeIs(
        'audit.logs.*',
        'password.change.logs',
        'users.password.history',
        'login.logs.*',
        'login.failed.logs',
        'http.logs.*'
    );
@endphp

@if(($menu['enabled'] ?? true) && $canView)
<li class="nav-item dropdown">
    <a class="nav-link dropdown-toggle{{ $isActive ? ' active' : '' }}"
       href="#"
       role="button"
       data-bs-toggle="dropdown"
       aria-expanded="false">
        <i class="{{ $menu['icon'] ?? 'bi-journal-text' }} me-1"></i>{{ $menu['label'] ?? 'Logs' }}
    </a>
    <ul class="dropdown-menu">
        @if($items['audit'] ?? true)
        <li>
            <a class="dropdown-item{{ request()->routeIs('audit.logs.*') ? ' active' : '' }}"
               href="{{ route('audit.logs.index') }}">
                <i class="bi bi-shield-check me-2"></i>Audit Logs
            </a>
        </li>
        @endif
        @if($items['password_history'] ?? true)
        <li>
            <a class="dropdown-item{{ request()->routeIs('password.change.logs', 'users.password.history') ? ' active' : '' }}"
               href="{{ route('password.change.logs') }}">
                <i class="bi bi-key me-2"></i>Password History
            </a>
        </li>
        @endif
        @if($items['failed_login'] ?? true)
        <li>
            <a class="dropdown-item{{ request()->routeIs('login.failed.logs') ? ' active' : '' }}"
               href="{{ route('login.failed.logs') }}">
                <i class="bi bi-exclamation-triangle me-2"></i>Failed Login Logs
            </a>
        </li>
        @endif
        @if($items['login_logout'] ?? true)
        <li>
            <a class="dropdown-item{{ request()->routeIs('login.logs.index') ? ' active' : '' }}"
               href="{{ route('login.logs.index') }}">
                <i class="bi bi-box-arrow-in-right me-2"></i>Login & Logout Logs
            </a>
        </li>
        @endif
        @if($items['http'] ?? true)
        <li>
            <a class="dropdown-item{{ request()->routeIs('http.logs.*') ? ' active' : '' }}"
               href="{{ route('http.logs.index') }}">
                <i class="bi bi-globe me-2"></i>HTTP Logs
            </a>
        </li>
        @endif
    </ul>
</li>
@endif
