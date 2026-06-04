@extends(config('security_suite.layout', config('audit.layout', 'layouts.app')))
@section('title', 'HTTP Logs')

@push('style')
<style>
.table-responsive { overflow: visible !important; }
.method-badge { font-size: 11px; font-weight: 700; padding: 3px 10px; border-radius: 6px; letter-spacing:.5px; }
.duration-cell { font-size: 12px; font-weight: 600; }
.url-cell { font-size: 12px; max-width: 260px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; display: block; }
.dropdown-menu { z-index: 9999 !important; border-radius: 12px; padding: 6px 0; }
.dropdown-menu .dropdown-item { padding: 8px 14px; display: flex; align-items: center; }
.no-caret::after { display: none !important; }
</style>
@endpush

@section('content')
<div class="dashboard-main">
    <div class="container-fluid px-0">
        <div class="page-wrapper">

            {{-- Header --}}
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
                <div>
                    <h1 class="page-title mb-1">HTTP Logs</h1>
                    <p class="page-subtitle mb-0">Track all incoming HTTP requests to the application</p>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-secondary fs-6">{{ number_format($logs->total()) }} total</span>
                    @if(config('security_suite.home_route'))
                    <a href="{{ route(config('security_suite.home_route')) }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-arrow-left me-1"></i>Back
                    </a>
                    @endif
                </div>
            </div>

            {{-- Filters --}}
            <form method="GET" action="{{ route('http.logs.index') }}" id="filterForm">
                <div class="mb-4">
                    <div class="row g-3 align-items-center">
                        <div class="col-lg-2 col-sm-6">
                            <div class="search-control">
                                <i class="bi bi-search"></i>
                                <input type="text" name="url_search" class="form-control" placeholder="Search URL or route" value="{{ request('url_search') }}">
                            </div>
                        </div>
                        <div class="col-lg-2 col-sm-6">
                            <select name="method_filter" class="form-select" onchange="this.form.submit()">
                                <option value="">All Methods</option>
                                @foreach(['GET','POST','PUT','PATCH','DELETE'] as $m)
                                    <option value="{{ $m }}" {{ request('method_filter') === $m ? 'selected' : '' }}>{{ $m }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-2 col-sm-6">
                            <select name="status_code" class="form-select" onchange="this.form.submit()">
                                <option value="">All Status</option>
                                @foreach([200,201,301,302,400,401,403,404,422,500] as $s)
                                    <option value="{{ $s }}" {{ request('status_code') == $s ? 'selected' : '' }}>{{ $s }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-2 col-sm-6">
                            <select name="user_id" class="form-select" onchange="this.form.submit()">
                                <option value="">All Users</option>
                                @foreach($users as $u)
                                    <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-2 col-sm-6">
                            <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}" onchange="this.form.submit()">
                        </div>

                        <div class="col-lg-2 text-lg-end">
                            <div class="d-flex gap-2 justify-content-lg-end">
                                <button id="searchBtn" class="btn btn-outline-success w-100 w-lg-auto">
                                    <i class="bi bi-search me-1"></i>Search
                                </button>
                                <button id="resetBtn" class="btn btn-outline-secondary w-100 w-lg-auto">
                                    <i class="bi bi-arrow-counterclockwise me-1"></i>Reset
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>

            {{-- Table --}}
            <div class="table-card shadow-sm">
                <div class="table-responsive">
                    <table class="table align-middle mb-0 table-striped">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Method</th>
                                <th>URL / Route</th>
                                <th>Status</th>
                                <th>User</th>
                                <th>IP</th>
                                <th>Duration</th>
                                <th>Date &amp; Time</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($logs as $i => $log)
                            @php
                                $methodColors = ['GET'=>'bg-primary','POST'=>'bg-success','PUT'=>'bg-warning text-dark','PATCH'=>'bg-info text-dark','DELETE'=>'bg-danger'];
                                $sc = $log->status_code;
                                $statusCls = $sc >= 500 ? 'bg-danger' : ($sc >= 400 ? 'bg-warning text-dark' : ($sc >= 300 ? 'bg-info text-dark' : 'bg-success'));
                                $d = $log->duration_ms;
                                $durCls = $d > 1000 ? 'text-danger' : ($d > 500 ? 'text-warning' : 'text-success');
                            @endphp
                            <tr>
                                <td>{{ $logs->firstItem() + $i }}</td>
                                <td>
                                    <span class="badge method-badge {{ $methodColors[$log->method] ?? 'bg-secondary' }}">
                                        {{ $log->method }}
                                    </span>
                                </td>
                                <td>
                                    <span class="url-cell" title="{{ $log->url }}">{{ $log->url }}</span>
                                    @if($log->route_name)
                                        <small class="text-muted d-block">{{ $log->route_name }}</small>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge {{ $statusCls }}">{{ $log->status_code ?? '—' }}</span>
                                </td>
                                <td>
                                    <small>{{ optional($log->user)->name ?? '<span class="text-muted">Guest</span>' }}</small>
                                </td>
                                <td><small class="text-muted">{{ $log->ip_address ?? '—' }}</small></td>
                                <td>
                                    <span class="duration-cell {{ $durCls }}">
                                        {{ $d ? $d.'ms' : '—' }}
                                    </span>
                                </td>
                                <td><small>{{ $log->created_at->format('d M Y, h:i A') }}</small></td>
                                <td class="text-end">
                                    <div class="dropdown">
                                        <button class="btn btn-light btn-sm dropdown-toggle no-caret" data-bs-toggle="dropdown">
                                            <i class="bi bi-three-dots-vertical"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <li>
                                                <a class="dropdown-item" href="{{ route('http.logs.show', $log->id) }}">
                                                    <i class="bi bi-eye me-2"></i>View Detail
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted py-5">
                                    <i class="bi bi-hdd-network" style="font-size:40px;opacity:.3;"></i>
                                    <p class="mt-2 mb-0">No HTTP logs found.</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                @if($logs->hasPages())
                <div class="table-footer d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <div class="page-selector d-flex align-items-center gap-2">
                        <span>Page</span>
                        <span class="fw-semibold">{{ $logs->currentPage() }}</span>
                        <span>of {{ $logs->lastPage() }}</span>
                        <small class="text-muted">({{ number_format($logs->total()) }} records)</small>
                    </div>
                    <div class="custom-pagination d-flex align-items-center gap-2">
                        @if($logs->onFirstPage())
                            <button class="page-arrow" disabled>&laquo;</button>
                            <button class="page-arrow" disabled>&lsaquo;</button>
                        @else
                            <a href="{{ $logs->url(1) }}" class="page-arrow">&laquo;</a>
                            <a href="{{ $logs->previousPageUrl() }}" class="page-arrow">&lsaquo;</a>
                        @endif

                        <div class="d-flex align-items-center gap-1">
                            @php
                                $start = max(1, $logs->currentPage() - 3);
                                $end   = min($logs->lastPage(), $logs->currentPage() + 3);
                            @endphp
                            @for($p = $start; $p <= $end; $p++)
                                <a href="{{ $logs->url($p) }}"
                                   class="btn btn-sm {{ $p === $logs->currentPage() ? 'btn-success' : 'btn-outline-success' }}">
                                    {{ $p }}
                                </a>
                            @endfor
                        </div>

                        @if($logs->hasMorePages())
                            <a href="{{ $logs->nextPageUrl() }}" class="page-arrow">&rsaquo;</a>
                            <a href="{{ $logs->url($logs->lastPage()) }}" class="page-arrow">&raquo;</a>
                        @else
                            <button class="page-arrow" disabled>&rsaquo;</button>
                            <button class="page-arrow" disabled>&raquo;</button>
                        @endif
                    </div>
                </div>
                @endif
            </div>

        </div>
    </div>
</div>
@endsection
