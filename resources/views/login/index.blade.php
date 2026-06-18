@extends(config('security_suite.layout', config('audit.layout', 'layouts.app')))
@section('title', $title)

@push('style')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<style>
.table-responsive { overflow: visible !important; }
table.dataTable td, table.dataTable th { overflow: visible !important; }
</style>
@endpush

@section('content')
<div class="dashboard-main">
    <div class="container-fluid px-0">
        <div class="page-wrapper">

            <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
                <div>
                    <h1 class="page-title mb-1">{{ $title }}</h1>
                    <p class="page-subtitle mb-0">
                        @if($pageKey === 'failed-login')
                            Track failed authentication attempts across the application
                        @else
                            Track successful logins and user logouts
                        @endif
                    </p>
                </div>
            </div>

            <div class="mb-4">
                <div class="row g-3 align-items-center">
                    <div class="col-lg-3">
                        <input type="text" id="filterEmail" class="form-control" placeholder="Search email" value="{{ request('email') }}">
                    </div>
                    <div class="col-lg-3 col-sm-6">
                        <select id="filterUser" class="form-select">
                            <option value="">All Users</option>
                            @foreach($users as $u)
                                <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>
                                    {{ $u->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-2 col-sm-6">
                        <input type="date" id="filterFrom" class="form-control" value="{{ request('from_date') }}">
                    </div>
                    <div class="col-lg-2 col-sm-6">
                        <input type="date" id="filterTo" class="form-control" value="{{ request('to_date') }}">
                    </div>
                    <div class="col-lg-2">
                        <div class="d-flex gap-2">
                            <button id="searchBtn" class="btn btn-outline-success w-100">
                                <i class="bi bi-search me-1"></i>Search
                            </button>
                            <button id="resetBtn" class="btn btn-outline-secondary w-100">
                                <i class="bi bi-arrow-counterclockwise"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="table-card shadow-sm">
                <div class="table-responsive">
                    <table id="loginLogTable" class="table align-middle mb-0 table-striped" style="width:100%">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Event</th>
                                <th>User</th>
                                <th>Email</th>
                                @if($pageKey === 'failed-login')
                                <th>Reason</th>
                                @endif
                                <th>IP Address</th>
                                <th>Date &amp; Time</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection

@push('script')
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script>
(function () {
    const rows = @json($logs->items());
    const pageKey = @json($pageKey);
    const indexRoute = pageKey === 'failed-login'
        ? @json(route('login.failed.logs'))
        : @json(route('login.logs.index'));

    if (!window.jQuery || !jQuery.fn.dataTable) return;

    jQuery(function ($) {
        const columns = [
            {
                data: null,
                render: function (data, type, row, meta) {
                    return String(meta.row + 1).padStart(2, '0');
                }
            },
            {
                data: 'event',
                render: function (data) {
                    const labels = {
                        login_success: ['Login', 'bg-success'],
                        login_failed: ['Failed', 'bg-danger'],
                        logout: ['Logout', 'bg-secondary']
                    };
                    const item = labels[data] || [data || '—', 'bg-secondary'];
                    return '<span class="badge ' + item[1] + '">' + item[0] + '</span>';
                }
            },
            {
                data: 'user',
                render: function (data) {
                    return (data && data.name) ? data.name : '<span class="text-muted">—</span>';
                }
            },
            {
                data: 'email',
                render: function (data) {
                    return data ? '<small>' + data + '</small>' : '<span class="text-muted">—</span>';
                }
            }
        ];

        if (pageKey === 'failed-login') {
            columns.push({
                data: 'failure_reason',
                render: function (data) {
                    return data ? '<small class="text-danger">' + data + '</small>' : '—';
                }
            });
        }

        columns.push(
            {
                data: 'ip_address',
                render: function (data) {
                    return '<small class="text-muted">' + (data || '—') + '</small>';
                }
            },
            {
                data: 'created_at',
                render: function (data) {
                    if (!data) return '—';
                    try {
                        const d = new Date(data);
                        return d.toLocaleString();
                    } catch (e) { return data; }
                }
            }
        );

        $('#loginLogTable').DataTable({
            data: rows,
            ordering: false,
            paging: true,
            pageLength: 20,
            lengthChange: false,
            info: true,
            searching: false,
            columns: columns
        });

        function goSearch() {
            const params = new URLSearchParams();
            const email  = $('#filterEmail').val();
            const userId = $('#filterUser').val();
            const from   = $('#filterFrom').val();
            const to     = $('#filterTo').val();
            if (email)  params.set('email', email);
            if (userId) params.set('user_id', userId);
            if (from)   params.set('from_date', from);
            if (to)     params.set('to_date', to);
            window.location.href = params.toString() ? indexRoute + '?' + params.toString() : indexRoute;
        }

        $('#searchBtn').on('click', goSearch);
        $('#resetBtn').on('click', function () { window.location.href = indexRoute; });
        $('#filterEmail').on('keydown', function (e) { if (e.key === 'Enter') { e.preventDefault(); goSearch(); } });
        $('#filterUser').on('change', goSearch);
    });
})();
</script>
@endpush
