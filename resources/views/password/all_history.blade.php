@extends(config('security_suite.layout', config('audit.layout', 'layouts.app')))
@section('title', 'Password Change Logs')

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
                    <h1 class="page-title mb-1">Password Change Logs</h1>
                    <p class="page-subtitle mb-0">Track password change history for all users</p>
                </div>
            </div>

            <div class="mb-4">
                <div class="row g-3 align-items-center">
                    <div class="col-lg-3">
                        <div class="search-control">
                            <i class="bi bi-search"></i>
                            <input type="text" id="searchUser" class="form-control" placeholder="Search user">
                        </div>
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
                    <div class="col-lg-3 text-lg-end">
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
                    <table id="pwdTable" class="table align-middle mb-0 table-striped" style="width:100%">
                        <thead>
                            <tr>
                                <th>Serial</th>
                                <th>User</th>
                                <th>Email</th>
                                <th>Total Changes</th>
                                <th>Last Changed At</th>
                                <th>Time Ago</th>
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
    const rows = @json($histories->items());

    if (!window.jQuery || !jQuery.fn.dataTable) return;

    jQuery(function ($) {

        const historyBase = '{{ route("users.password.history", ":id") }}';

        const dt = $('#pwdTable').DataTable({
            data: rows,
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
                    data: 'user',
                    render: function (data) {
                        return data && data.name ? '<strong>' + data.name + '</strong>' : '<span class="text-muted">N/A</span>';
                    }
                },
                {
                    data: 'user',
                    render: function (data) {
                        return data && data.email ? '<small class="text-muted">' + data.email + '</small>' : '<small class="text-muted">—</small>';
                    }
                },
                {
                    data: 'change_count',
                    render: function (data) {
                        return '<span class="badge bg-secondary">' + (data || 0) + '</span>';
                    }
                },
                {
                    data: 'last_changed_at',
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
                    data: 'last_changed_at',
                    render: function (data) {
                        if (!data) return '—';
                        try {
                            const diff = Date.now() - new Date(data).getTime();
                            const mins  = Math.floor(diff / 60000);
                            const hours = Math.floor(mins / 60);
                            const days  = Math.floor(hours / 24);
                            if (days > 0)  return '<small class="text-muted">' + days  + ' day'  + (days  > 1 ? 's' : '') + ' ago</small>';
                            if (hours > 0) return '<small class="text-muted">' + hours + ' hour' + (hours > 1 ? 's' : '') + ' ago</small>';
                            if (mins > 0)  return '<small class="text-muted">' + mins  + ' min'  + (mins  > 1 ? 's' : '') + ' ago</small>';
                            return '<small class="text-muted">Just now</small>';
                        } catch (e) { return '—'; }
                    }
                },
                {
                    data: 'user',
                    className: 'text-end',
                    render: function (data) {
                        if (!data || !data.id) return '';
                        const url = historyBase.replace(':id', data.id);
                        return '<div class="dropdown">' +
                            '<button class="btn btn-light btn-sm dropdown-toggle no-caret" data-bs-toggle="dropdown">' +
                            '<i class="bi bi-three-dots-vertical"></i></button>' +
                            '<ul class="dropdown-menu dropdown-menu-end">' +
                            '<li><a class="dropdown-item" href="' + url + '"><i class="bi bi-clock-history me-2"></i>View History</a></li>' +
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

        /* ── Client-side search on name/email ── */
        $('#searchUser').on('input', function () {
            const q = $(this).val().toLowerCase();
            dt.rows().every(function () {
                const d = this.data();
                const name  = (d.user && d.user.name  ? d.user.name.toLowerCase()  : '');
                const email = (d.user && d.user.email ? d.user.email.toLowerCase() : '');
                $(this.node()).toggle(name.includes(q) || email.includes(q));
            });
        });

        /* ── Server-side user filter + reset ── */
        function goSearch() {
            const params  = new URLSearchParams();
            const userId  = $('#filterUser').val();
            if (userId) params.set('user_id', userId);
            const base = '{{ route("password.change.logs") }}';
            window.location.href = params.toString() ? base + '?' + params.toString() : base;
        }

        $('#searchBtn').on('click', goSearch);
        $('#resetBtn').on('click', function () { window.location.href = '{{ route("password.change.logs") }}'; });
        $('#filterUser').on('change', goSearch);
    });
})();
</script>
@endpush
