<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('page_visits', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('visitor_session_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->text('url');
            $table->string('route_name')->nullable()->index();
            $table->string('page_title')->nullable();
            $table->string('method', 10);
            $table->timestamp('visited_at')->index();
            $table->unsignedInteger('response_time')->nullable();
            $table->timestamps();

            $table->index(['visitor_session_id', 'visited_at']);
            $table->index(['user_id', 'visited_at']);
            $table->index(['method', 'visited_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('page_visits');
    }
};
