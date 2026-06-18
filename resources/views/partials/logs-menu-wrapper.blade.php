@php
    $mode = config('security_suite.menu.mode', 'dropdown');
@endphp

@if($mode === 'sidebar')
    @include('security-suite::partials.logs-menu-sidebar')
@else
    @include('security-suite::partials.logs-menu')
@endif
