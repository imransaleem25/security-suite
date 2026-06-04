<?php

namespace ImranSaleem\SecuritySuite\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use ImranSaleem\SecuritySuite\Models\PasswordHistory;
use ImranSaleem\SecuritySuite\Services\PasswordPolicyService;
use ImranSaleem\SecuritySuite\Services\AuditService;

class PasswordController extends Controller
{
    protected PasswordPolicyService $policy;
    protected AuditService $audit;

    public function __construct(PasswordPolicyService $policy, AuditService $audit)
    {
        $this->policy = $policy;
        $this->audit  = $audit;
    }

    public function showExpired()
    {
        return view('security-suite::password.expired');
    }

    public function updateExpired(Request $request)
    {
        $request->validate([
            'new_password' => ['required', 'confirmed', $this->policy->complexityRule()],
        ]);

        $user = Auth::user();

        if ($this->policy->isReused($user, $request->new_password)) {
            return back()->withErrors(['new_password' => 'You cannot reuse any of your last ' . config('password_policy.history_count', 2) . ' passwords.']);
        }

        $this->policy->saveToHistory($user);
        $user->password = Hash::make($request->new_password);
        $this->policy->markChanged($user);

        $this->audit->log('users', 'password_expired_changed', $user, [], ['changed_at' => now()->toDateTimeString()]);

        $homeRoute = config('security_suite.home_route');
        $redirect = $homeRoute
            ? redirect()->route($homeRoute)
            : redirect()->intended('/');

        return $redirect->with('success', 'Password updated successfully.');
    }

    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => ['required'],
            'new_password'     => ['required', 'confirmed', $this->policy->complexityRule()],
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = Auth::user();

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json(['errors' => ['current_password' => ['Current password is incorrect.']]], 422);
        }

        if ($this->policy->isReused($user, $request->new_password)) {
            return response()->json(['errors' => ['new_password' => ['You cannot reuse any of your last ' . config('password_policy.history_count', 2) . ' passwords.']]], 422);
        }

        $this->policy->saveToHistory($user);
        $user->password = Hash::make($request->new_password);
        $this->policy->markChanged($user);

        $this->audit->log('users', 'password_changed', $user, [], ['changed_at' => now()->toDateTimeString()]);

        return response()->json(['success' => 'Password changed successfully.']);
    }

    public function history(Request $request, $userId)
    {
        abort_unless(is_numeric($userId), 404);

        $userModel = app(config('auth.providers.users.model'));
        $user      = $userModel->findOrFail((int) $userId);
        $histories = PasswordHistory::where('user_id', (int) $userId)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('security-suite::password.history', compact('user', 'histories'));
    }

    public function allHistory(Request $request)
    {
        $userId = ($request->filled('user_id') && is_numeric($request->user_id))
            ? (int) $request->user_id
            : null;

        $query = PasswordHistory::with('user')
            ->select(
                'user_id',
                DB::raw('MAX(created_at) as last_changed_at'),
                DB::raw('COUNT(*) as change_count')
            )
            ->groupBy('user_id')
            ->orderBy('last_changed_at', 'desc');

        if ($userId) {
            $query->where('user_id', $userId);
        }

        $histories = $query->paginate(20)->withQueryString();
        $users     = app(config('auth.providers.users.model'))->orderBy('name')->get(['id', 'name']);

        return view('security-suite::password.all_history', compact('histories', 'users'));
    }
}
