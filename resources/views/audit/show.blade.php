@extends(config('security_suite.layout', config('audit.layout', 'layouts.app')))
@section('title', 'Audit Log Detail')

@section('content')
<div class="dashboard-main">
    <div class="container-fluid px-0">
        <div class="page-wrapper">

            <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
                <div>
                    <h1 class="page-title mb-1">Audit Log Detail</h1>
                    <p class="page-subtitle mb-0">Full detail of the recorded action</p>
                </div>
                <a href="{{ route('audit.logs.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i>Back
                </a>
            </div>

            <div class="table-card shadow-sm mb-4 p-4">
                <div class="row g-4">
                    @php
                        $moduleColors = [
                            'users' => 'bg-success', 'roles' => 'bg-dark', 'permissions' => 'bg-dark',
                        ];
                        $actionColors = [
                            'created' => 'bg-success', 'updated' => 'bg-primary', 'deleted' => 'bg-danger',
                            'password_changed' => 'bg-primary', 'password_expired_changed' => 'bg-primary',
                            'account_locked_temporary' => 'bg-danger', 'account_blocked_permanent' => 'bg-danger',
                            'account_unblocked' => 'bg-success', 'permissions_synced' => 'bg-info text-dark',
                        ];
                        $modCls    = $moduleColors[$auditLog->module] ?? 'bg-secondary';
                        $actionCls = $actionColors[$auditLog->action] ?? 'bg-secondary';
                    @endphp
                    <div class="col-md-4 col-sm-6">
                        <p class="text-muted small text-uppercase fw-semibold mb-1">Module</p>
                        <span class="badge {{ $modCls }} fs-6">
                            {{ ucfirst(str_replace('_', ' ', $auditLog->module)) }}
                        </span>
                    </div>
                    <div class="col-md-4 col-sm-6">
                        <p class="text-muted small text-uppercase fw-semibold mb-1">Action</p>
                        <span class="badge {{ $actionCls }} fs-6">
                            {{ ucfirst(str_replace('_', ' ', $auditLog->action)) }}
                        </span>
                    </div>
                    <div class="col-md-4 col-sm-6">
                        <p class="text-muted small text-uppercase fw-semibold mb-1">Record</p>
                        <p class="mb-0">
                            <strong>{{ $auditLog->auditable_name ?? 'N/A' }}</strong>
                            <small class="text-muted ms-1">(ID: {{ $auditLog->auditable_id ?? '—' }})</small>
                        </p>
                    </div>
                    <div class="col-md-4 col-sm-6">
                        <p class="text-muted small text-uppercase fw-semibold mb-1">Performed By</p>
                        <p class="mb-0">{{ optional($auditLog->user)->name ?? 'System' }}</p>
                    </div>
                    <div class="col-md-4 col-sm-6">
                        <p class="text-muted small text-uppercase fw-semibold mb-1">IP Address</p>
                        <p class="mb-0">{{ $auditLog->ip_address ?? '—' }}</p>
                    </div>
                    <div class="col-md-4 col-sm-6">
                        <p class="text-muted small text-uppercase fw-semibold mb-1">Date &amp; Time</p>
                        <p class="mb-0">{{ $auditLog->created_at->format('d M Y, h:i:s A') }}</p>
                    </div>
                </div>
            </div>

            @php
                $hasOld = !empty($auditLog->old_values);
                $hasNew = !empty($auditLog->new_values);
            @endphp
            @if($hasOld || $hasNew)
            <div class="row g-4">

                @if($hasOld)
                <div class="{{ $hasNew ? 'col-md-6' : 'col-12' }}">
                    <div class="table-card shadow-sm p-4">
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <span class="badge bg-danger">&nbsp;</span>
                            <h6 class="mb-0 fw-bold text-danger">Before</h6>
                        </div>
                        <table class="table table-sm mb-0">
                            <thead>
                                <tr>
                                    <th style="width:40%">Field</th>
                                    <th>Value</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($auditLog->old_values as $field => $value)
                                <tr>
                                    <td><span class="text-muted small fw-semibold">{{ ucfirst(str_replace('_', ' ', $field)) }}</span></td>
                                    <td>{{ is_array($value) ? implode(', ', $value) : $value }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @endif

                @if($hasNew)
                <div class="{{ $hasOld ? 'col-md-6' : 'col-12' }}">
                    <div class="table-card shadow-sm p-4">
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <span class="badge bg-success">&nbsp;</span>
                            <h6 class="mb-0 fw-bold text-success">After</h6>
                        </div>
                        <table class="table table-sm mb-0">
                            <thead>
                                <tr>
                                    <th style="width:40%">Field</th>
                                    <th>Value</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($auditLog->new_values as $field => $value)
                                @php $changed = $hasOld && isset($auditLog->old_values[$field]) && $auditLog->old_values[$field] != $value; @endphp
                                <tr class="{{ $changed ? 'table-warning' : '' }}">
                                    <td>
                                        <span class="text-muted small fw-semibold">{{ ucfirst(str_replace('_', ' ', $field)) }}</span>
                                    </td>
                                    <td>
                                        {{ is_array($value) ? implode(', ', $value) : $value }}
                                        @if($changed)
                                            <span class="badge bg-warning text-dark ms-1">changed</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @endif

            </div>
            @endif

        </div>
    </div>
</div>
@endsection
