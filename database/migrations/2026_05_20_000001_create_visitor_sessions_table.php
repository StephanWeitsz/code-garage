<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('visitor_sessions', function (Blueprint $table): void {
            $table->id();
            $table->string('session_id', 120)->unique();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->text('referrer')->nullable();
            $table->string('device_type', 30)->nullable();
            $table->string('browser', 120)->nullable();
            $table->string('platform', 120)->nullable();
            $table->string('country', 120)->nullable();
            $table->string('city', 120)->nullable();
            $table->timestamp('first_seen_at')->nullable()->index();
            $table->timestamp('last_seen_at')->nullable()->index();
            $table->timestamps();

            $table->index(['user_id', 'last_seen_at']);
            $table->index(['device_type', 'last_seen_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visitor_sessions');
    }
};
