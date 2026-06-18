@auth
@if(config('security_suite.idle_timeout_minutes', 15) > 0)
<script src="{{ asset('vendor/security-suite/idle-timeout.js') }}"
        data-config-url="{{ route('idle.config') }}"
        data-ping-url="{{ route('idle.ping') }}"
        defer></script>
@endif
@endauth
