<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedTinyInteger('login_attempts')->default(0)->after('password');
            $table->timestamp('locked_until')->nullable()->after('login_attempts');
            $table->boolean('is_blocked')->default(false)->after('locked_until');
            $table->timestamp('password_changed_at')->nullable()->after('is_blocked');
            $table->boolean('forced_change_password')->default(false)->after('password_changed_at');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['login_attempts', 'locked_until', 'is_blocked', 'password_changed_at', 'forced_change_password']);
        });
    }
};
