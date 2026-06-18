<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('login_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('email')->nullable();
            $table->string('event', 50);
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('failure_reason')->nullable();
            $table->timestamps();

            $table->index('user_id', 'll_user_idx');
            $table->index('event', 'll_event_idx');
            $table->index('created_at', 'll_created_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('login_logs');
    }
};
