<?php

use Illuminate\Support\Facades\Route;
use ImranSaleem\SecuritySuite\Http\Controllers\AuditLogController;
use ImranSaleem\SecuritySuite\Http\Controllers\PasswordController;
use ImranSaleem\SecuritySuite\Http\Controllers\IdleController;
use ImranSaleem\SecuritySuite\Http\Controllers\HttpLogController;
use ImranSaleem\SecuritySuite\Http\Controllers\LoginLogController;

$prefix = config('security_suite.route_prefix', 'security');

Route::prefix($prefix)->middleware(['web'])->group(function () {

    Route::middleware(['auth'])->group(function () {
        Route::get('/idle-ping', [IdleController::class, 'ping'])->name('idle.ping');
        Route::get('/idle-config', [IdleController::class, 'config'])->name('idle.config');
        Route::get('/password-expired', [PasswordController::class, 'showExpired'])->name('password.expired');
        Route::post('/password-expired', [PasswordController::class, 'updateExpired'])->name('password.expired.update');
        Route::post('/change-password', [PasswordController::class, 'changePassword'])->name('password.change');
    });

    Route::middleware(['auth', 'role:' . config('audit.viewer_role', 'admin')])->group(function () {
        Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit.logs.index');
        Route::get('/audit-logs/{auditLog}', [AuditLogController::class, 'show'])->name('audit.logs.show');
        Route::get('/password-change-logs', [PasswordController::class, 'allHistory'])->name('password.change.logs');
        Route::get('/users/{userId}/password-history', [PasswordController::class, 'history'])->name('users.password.history');
        Route::get('/http-logs', [HttpLogController::class, 'index'])->name('http.logs.index');
        Route::get('/http-logs/{httpLog}', [HttpLogController::class, 'show'])->name('http.logs.show');
        Route::get('/login-logs', [LoginLogController::class, 'index'])->name('login.logs.index');
        Route::get('/failed-login-logs', [LoginLogController::class, 'failed'])->name('login.failed.logs');
    });
});
