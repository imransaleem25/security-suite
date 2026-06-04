@extends(config('security_suite.password_expired_layout', config('security_suite.layout', 'layouts.guest')))
@section('title', 'Change Password')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <h2 class="h4 text-center mb-2">Change Password</h2>
                    <p class="text-center text-muted small mb-4">
                        @if(auth()->check() && auth()->user()->forced_change_password)
                            Your password was reset by an administrator. Set a new password to continue.
                        @else
                            Your password has expired. Set a new password to continue.
                        @endif
                    </p>

                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('password.expired.update') }}" id="expiredPasswordForm">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label" for="newPwd">New Password</label>
                            <input type="password" name="new_password" id="newPwd"
                                   class="form-control @error('new_password') is-invalid @enderror"
                                   required autocomplete="new-password">
                            @error('new_password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="invalid-feedback" id="newPwd-error"></div>
                            <small class="text-muted">
                                Min {{ config('password_policy.min_length', 12) }} characters with uppercase, lowercase, number, and symbol.
                                Cannot reuse your last {{ config('password_policy.history_count', 2) }} passwords.
                            </small>
                        </div>
                        <div class="mb-4">
                            <label class="form-label" for="confirmPwd">Confirm New Password</label>
                            <input type="password" name="new_password_confirmation" id="confirmPwd"
                                   class="form-control" required autocomplete="new-password">
                            <div class="invalid-feedback" id="confirmPwd-error"></div>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Update Password</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const newPwd     = document.getElementById('newPwd');
    const confirmPwd = document.getElementById('confirmPwd');
    const newErr     = document.getElementById('newPwd-error');
    const confErr    = document.getElementById('confirmPwd-error');
    const minLen     = {{ (int) config('password_policy.min_length', 12) }};

    function validateNew() {
        const v = newPwd.value;
        let err = '';
        if (v.length < minLen)        err = 'Minimum ' + minLen + ' characters required.';
        else if (!/[a-z]/.test(v))    err = 'Must contain a lowercase letter.';
        else if (!/[A-Z]/.test(v))    err = 'Must contain an uppercase letter.';
        else if (!/[0-9]/.test(v))    err = 'Must contain a number.';
        else if (!/[\W_]/.test(v))    err = 'Must contain a special character.';
        if (err) {
            newPwd.classList.add('is-invalid');
            newErr.textContent = err;
            return false;
        }
        newPwd.classList.remove('is-invalid');
        newErr.textContent = '';
        return true;
    }

    function validateConfirm() {
        if (newPwd.value !== confirmPwd.value || !confirmPwd.value) {
            confirmPwd.classList.add('is-invalid');
            confErr.textContent = 'Passwords do not match.';
            return false;
        }
        confirmPwd.classList.remove('is-invalid');
        confErr.textContent = '';
        return true;
    }

    newPwd.addEventListener('input', function () { validateNew(); if (confirmPwd.value) validateConfirm(); });
    confirmPwd.addEventListener('input', validateConfirm);
    document.getElementById('expiredPasswordForm').addEventListener('submit', function (e) {
        if (!validateNew() || !validateConfirm()) e.preventDefault();
    });
});
</script>
@endpush
