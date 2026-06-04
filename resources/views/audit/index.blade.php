@extends(config('security_suite.layout', config('audit.layout', 'layouts.app')))
@section('title', 'Audit Logs')

@push('style')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<style>
.table-responsive { overflow: visible !important; }
table.dataTable td, table.dataTable th { overflow: visible !important; }
.dropdown-menu { z-index: 9999 !important; border-radius: 12px; padding: 6px 0; }
.dropdown-menu .dropdown-item { padding: 8px 14px; display: flex; align-items: center; }
.dropdown-menu .dropdown-item i { font-size: 16px; opacity: .85; }
.no-caret::after { display: none !important; }
</style>
@endpush

@section('content')
<div class="dashboard-main">
    <div class="container-fluid px-0">
        <div class="page-wrapper">

            <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
                <div>
                    <h1 class="page-title mb-1">Audit Logs</h1>
                    <p class="page-subtitle mb-0">Track all create, update and delete actions across the system</p>
                </div>
            </div>

            <div class="mb-4">
                <div class="row g-3 align-items-center">
                    <div class="col-lg-2">
                        <div class="search-control">
                            <i class="bi bi-search"></i>
                            <input type="text" id="searchRecord" class="form-control" placeholder="Search record" value="{{ request('searchRecord') }}">
                        </div>
                    </div>
                    <div class="col-lg-2 col-sm-6">
                        <select id="filterModule" class="form-select">
                            <option value="">All Modules</option>
                            @foreach($modules as $m)
                                <option value="{{ $m }}" {{ request('module') === $m ? 'selected' : '' }}>
                                    {{ ucfirst(str_replace('_', ' ', $m)) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-2 col-sm-6">
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

            <div class="table-card shadow-sm">
                <div class="table-responsive">
                    <table id="auditTable" class="table align-middle mb-0 table-striped" style="width:100%">
                        <thead>
                            <tr>
                                <th>Serial</th>
                                <th>Module</th>
                                <th>Action</th>
                                <th>Record</th>
                                <th>Performed By</th>
                                <th>IP Address</th>
                                <th>Date &amp; Time</th>
                                <th class="text-end">Action</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>

                <div class="table-footer d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <div class="page-selector d-flex align-items-center gap-2">
                        <span>Page</span>
                        <select id="pageSelect" class="form-select form-select-sm"></select>
                        <span>of <span id="totalPages">1</span></span>
                    </div>
                    <div class="custom-pagination d-flex align-items-center gap-2">
                        <button class="page-arrow" data-action="first" type="button">&laquo;</button>
                        <button class="page-arrow" data-action="prev"  type="button">&lsaquo;</button>
                        <div class="number-buttons d-flex align-items-center gap-1"></div>
                        <button class="page-arrow" data-action="next"  type="button">&rsaquo;</button>
                        <button class="page-arrow" data-action="last"  type="button">&raquo;</button>
                    </div>
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
    const logs = @json($logs->items());

    if (!window.jQuery || !jQuery.fn.dataTable) return;

    jQuery(function ($) {

        const showBase = '{{ route("audit.logs.show", ":id") }}';

        const dt = $('#auditTable').DataTable({
            data: logs,
            ordering: false,
            paging: true,
            pageLength: 10,
            lengthChange: false,
            info: false,
            searching: false,
            columns: [
                {
                    data: null,
                    render: function (data, type, row, meta) {
                        return String(meta.row + 1).padStart(2, '0');
                    }
                },
                {
                    data: 'module',
                    render: function (data) {
                        if (!data) return '—';
                        const label = data.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase());
                        const colors = {
                            users: 'bg-success', roles: 'bg-dark', permissions: 'bg-dark'
                        };
                        const cls = colors[data] || 'bg-secondary';
                        return '<span class="badge ' + cls + '">' + label + '</span>';
                    }
                },
                {
                    data: 'action',
                    render: function (data) {
                        if (!data) return '—';
                        const label = data.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase());
                        const colors = {
                            created: 'bg-success', updated: 'bg-primary', deleted: 'bg-danger',
                            password_changed: 'bg-primary', password_expired_changed: 'bg-primary',
                            account_locked_temporary: 'bg-danger', account_blocked_permanent: 'bg-danger',
                            account_unblocked: 'bg-success', permissions_synced: 'bg-info text-dark'
                        };
                        const cls = colors[data] || 'bg-secondary';
                        return '<span class="badge ' + cls + '">' + label + '</span>';
                    }
                },
                {
                    data: 'auditable_name',
                    render: function (data, type, row) {
                        const name = data || 'N/A';
                        const id   = row.auditable_id || '—';
                        return '<strong>' + name + '</strong><br><small class="text-muted">ID: ' + id + '</small>';
                    }
                },
                {
                    data: 'user',
                    render: function (data) {
                        return (data && data.name) ? data.name : '<span class="text-muted">System</span>';
                    }
                },
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
                            const day = String(d.getDate()).padStart(2, '0');
                            const months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
                            const month = months[d.getMonth()];
                            const year  = d.getFullYear();
                            let h = d.getHours();
                            const m = String(d.getMinutes()).padStart(2, '0');
                            const ampm = h >= 12 ? 'PM' : 'AM';
                            h = h % 12 || 12;
                            return day + '-' + month + '-' + year + ' ' + h + ':' + m + ' ' + ampm;
                        } catch (e) { return data; }
                    }
                },
                {
                    data: null,
                    className: 'text-end',
                    render: function (data, type, row) {
                        const url = showBase.replace(':id', row.id);
                        return '<div class="dropdown">' +
                            '<button class="btn btn-light btn-sm dropdown-toggle no-caret" data-bs-toggle="dropdown">' +
                            '<i class="bi bi-three-dots-vertical"></i></button>' +
                            '<ul class="dropdown-menu dropdown-menu-end">' +
                            '<li><a class="dropdown-item" href="' + url + '"><i class="bi bi-eye me-2"></i>View Detail</a></li>' +
                            '</ul></div>';
                    }
                },
                { data: null, orderable: false, defaultContent: '' }
            ]
        });

        /* ── Pagination ── */
        const $footer     = $('.table-footer');
        const $pageSelect = $('#pageSelect');
        const $totalPages = $('#totalPages');
        const $numButtons = $('.number-buttons');

        function renderPager() {
            const info       = dt.page.info();
            const totalPages = info.pages || 1;
            const current    = info.page;

            $totalPages.text(totalPages);
            $pageSelect.empty();
            for (let i = 0; i < totalPages; i++) {
                $pageSelect.append('<option value="' + i + '"' + (i === current ? ' selected' : '') + '>' + (i + 1) + '</option>');
            }

            $numButtons.empty();
            const windowSize = 7;
            const start = Math.max(0, Math.min(current - Math.floor(windowSize / 2), totalPages - windowSize));
            const end   = Math.min(totalPages, start + windowSize);
            for (let i = start; i < end; i++) {
                const cls = i === current ? 'btn-success' : 'btn-outline-success';
                $numButtons.append('<button class="btn btn-sm ' + cls + '" data-page-index="' + i + '">' + (i + 1) + '</button>');
            }

            $footer.find('[data-action="first"],[data-action="prev"]').prop('disabled', current <= 0);
            $footer.find('[data-action="next"],[data-action="last"]').prop('disabled', current >= totalPages - 1);
        }

        dt.on('draw', renderPager);
        renderPager();

        $footer.on('click', '.page-arrow', function () {
            const action = $(this).data('action');
            const info   = dt.page.info();
            const last   = Math.max(0, (info.pages || 1) - 1);
            if      (action === 'first') dt.page(0).draw('page');
            else if (action === 'prev')  dt.page(Math.max(0, info.page - 1)).draw('page');
            else if (action === 'next')  dt.page(Math.min(last, info.page + 1)).draw('page');
            else if (action === 'last')  dt.page(last).draw('page');
        });

        $numButtons.on('click', 'button[data-page-index]', function () {
            const idx = parseInt($(this).data('page-index'), 10);
            if (!isNaN(idx)) dt.page(idx).draw('page');
        });

        $pageSelect.on('change', function () {
            const idx = parseInt($(this).val(), 10);
            if (!isNaN(idx)) dt.page(idx).draw('page');
        });

        /* ── Filters ── */
        function goSearch() {
            const params = new URLSearchParams();
            const record = $('#searchRecord').val();
            const module = $('#filterModule').val();
            const userId = $('#filterUser').val();
            const from   = $('#filterFrom').val();
            const to     = $('#filterTo').val();
            if (record) params.set('searchRecord', record);
            if (module) params.set('module', module);
            if (userId) params.set('user_id', userId);
            if (from)   params.set('from_date', from);
            if (to)     params.set('to_date', to);
            const base = '{{ route("audit.logs.index") }}';
            window.location.href = params.toString() ? base + '?' + params.toString() : base;
        }

        $('#searchBtn').on('click', goSearch);
        $('#resetBtn').on('click', function () { window.location.href = '{{ route("audit.logs.index") }}'; });
        $('#searchRecord').on('keydown', function (e) { if (e.key === 'Enter') { e.preventDefault(); goSearch(); } });
        $('#filterModule,#filterUser').on('change', goSearch);
    });
})();
</script>
@endpush
