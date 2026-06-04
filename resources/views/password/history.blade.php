@extends(config('security_suite.layout', config('audit.layout', 'layouts.app')))
@section('title', 'Password History')

@push('style')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<style>
.table-responsive { overflow: visible !important; }
table.dataTable td, table.dataTable th { overflow: visible !important; }
.no-caret::after { display: none !important; }
</style>
@endpush

@section('content')
<div class="dashboard-main">
    <div class="container-fluid px-0">
        <div class="page-wrapper">

            <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
                <div>
                    <h1 class="page-title mb-1">Password Change History</h1>
                    <p class="page-subtitle mb-0">{{ $user->name }} &mdash; {{ $user->email }}</p>
                </div>
                <a href="{{ route('password.change.logs') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i>Back
                </a>
            </div>

            <div class="table-card shadow-sm">
                <div class="table-responsive">
                    <table id="historyTable" class="table align-middle mb-0 table-striped" style="width:100%">
                        <thead>
                            <tr>
                                <th>Serial</th>
                                <th>Changed At</th>
                                <th>Hash (masked)</th>
                                <th>Time Ago</th>
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
    const rows = @json($histories);

    if (!window.jQuery || !jQuery.fn.dataTable) return;

    jQuery(function ($) {

        const dt = $('#historyTable').DataTable({
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
                    data: 'password',
                    render: function (data) {
                        if (!data) return '—';
                        return '<code class="text-muted" style="font-size:11px">' + data.substring(0, 20) + '••••••••••••••••••••</code>';
                    }
                },
                {
                    data: 'created_at',
                    render: function (data) {
                        if (!data) return '—';
                        try {
                            const diff  = Date.now() - new Date(data).getTime();
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
    });
})();
</script>
@endpush
