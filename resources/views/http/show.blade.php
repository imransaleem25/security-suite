@extends(config('security_suite.layout', config('audit.layout', 'layouts.app')))
@section('title', 'HTTP Log Detail')

@push('style')
<style>
pre { background:#f8f9fa; border:1px solid #e9ecef; border-radius:8px; padding:12px; font-size:12px; max-height:300px; overflow-y:auto; }
</style>
@endpush

@section('content')
<div class="dashboard-main">
    <div class="container-fluid px-0">
        <div class="page-wrapper">

            <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
                <div>
                    <h1 class="page-title mb-1">HTTP Log Detail</h1>
                    <p class="page-subtitle mb-0">Full detail of the recorded HTTP request</p>
                </div>
                <a href="{{ route('http.logs.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i>Back
                </a>
            </div>

            {{-- Meta --}}
            <div class="table-card shadow-sm mb-4 p-4">
                <div class="row g-4">
                    @php
                        $methodColors = ['GET'=>'bg-primary','POST'=>'bg-success','PUT'=>'bg-warning text-dark','PATCH'=>'bg-info text-dark','DELETE'=>'bg-danger'];
                        $statusCode   = $httpLog->status_code;
                        $statusCls    = $statusCode >= 500 ? 'bg-danger' : ($statusCode >= 400 ? 'bg-warning text-dark' : ($statusCode >= 300 ? 'bg-info text-dark' : 'bg-success'));
                    @endphp
                    <div class="col-md-2 col-sm-6">
                        <p class="text-muted small text-uppercase fw-semibold mb-1">Method</p>
                        <span class="badge {{ $methodColors[$httpLog->method] ?? 'bg-secondary' }} fs-6">{{ $httpLog->method }}</span>
                    </div>
                    <div class="col-md-2 col-sm-6">
                        <p class="text-muted small text-uppercase fw-semibold mb-1">Status</p>
                        <span class="badge {{ $statusCls }} fs-6">{{ $httpLog->status_code ?? '—' }}</span>
                    </div>
                    <div class="col-md-2 col-sm-6">
                        <p class="text-muted small text-uppercase fw-semibold mb-1">Duration</p>
                        @php $d = $httpLog->duration_ms; $dc = $d > 1000 ? 'text-danger' : ($d > 500 ? 'text-warning' : 'text-success'); @endphp
                        <p class="mb-0 {{ $dc }} fw-semibold">{{ $d ? $d.'ms' : '—' }}</p>
                    </div>
                    <div class="col-md-6 col-sm-12">
                        <p class="text-muted small text-uppercase fw-semibold mb-1">URL</p>
                        <p class="mb-0" style="word-break:break-all;font-size:13px;">{{ $httpLog->url }}</p>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <p class="text-muted small text-uppercase fw-semibold mb-1">Route Name</p>
                        <p class="mb-0">{{ $httpLog->route_name ?? '—' }}</p>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <p class="text-muted small text-uppercase fw-semibold mb-1">User</p>
                        <p class="mb-0">{{ optional($httpLog->user)->name ?? 'Guest' }}</p>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <p class="text-muted small text-uppercase fw-semibold mb-1">IP Address</p>
                        <p class="mb-0">{{ $httpLog->ip_address ?? '—' }}</p>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <p class="text-muted small text-uppercase fw-semibold mb-1">Date &amp; Time</p>
                        <p class="mb-0">{{ $httpLog->created_at->format('d M Y, h:i:s A') }}</p>
                    </div>
                    <div class="col-12">
                        <p class="text-muted small text-uppercase fw-semibold mb-1">User Agent</p>
                        <p class="mb-0" style="font-size:12px;word-break:break-all;">{{ $httpLog->user_agent ?? '—' }}</p>
                    </div>
                </div>
            </div>

            {{-- Payload --}}
            @if($httpLog->request_payload || $httpLog->response_payload)
            <div class="row g-4">
                @if($httpLog->request_payload)
                <div class="{{ $httpLog->response_payload ? 'col-md-6' : 'col-12' }}">
                    <div class="table-card shadow-sm p-4">
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <span class="badge bg-primary">&nbsp;</span>
                            <h6 class="mb-0 fw-bold text-primary">Request Payload</h6>
                        </div>
                        <pre>{{ json_encode($httpLog->request_payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                    </div>
                </div>
                @endif
                @if($httpLog->response_payload)
                <div class="{{ $httpLog->request_payload ? 'col-md-6' : 'col-12' }}">
                    <div class="table-card shadow-sm p-4">
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <span class="badge bg-success">&nbsp;</span>
                            <h6 class="mb-0 fw-bold text-success">Response Payload</h6>
                        </div>
                        <pre>{{ json_encode($httpLog->response_payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                    </div>
                </div>
                @endif
            </div>
            @endif

        </div>
    </div>
</div>
@endsection
