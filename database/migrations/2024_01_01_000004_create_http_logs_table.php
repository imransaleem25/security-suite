<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('http_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('method', 10);
            $table->text('url');
            $table->string('route_name', 255)->nullable();
            $table->unsignedSmallInteger('status_code')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->json('request_payload')->nullable();
            $table->json('response_payload')->nullable();
            $table->unsignedInteger('duration_ms')->nullable();
            $table->timestamps();
            $table->index('user_id', 'hl_user_idx');
            $table->index('created_at', 'hl_created_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('http_logs');
    }
};
