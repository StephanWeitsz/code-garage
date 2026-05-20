<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('course_views', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('visitor_session_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedBigInteger('course_id')->nullable();
            $table->string('course_slug')->nullable();
            $table->string('course_title')->nullable();
            $table->string('event_type', 40)->default('view');
            $table->timestamp('occurred_at')->index();
            $table->timestamps();

            $table->index(['course_id', 'event_type', 'occurred_at']);
            $table->index(['course_slug', 'event_type', 'occurred_at']);
            $table->index(['visitor_session_id', 'occurred_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_views');
    }
};
