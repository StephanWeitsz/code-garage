<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('page_visits', function (Blueprint $table): void {
            $table->string('request_host')->nullable()->after('url')->index();
            $table->boolean('is_suspicious')->default(false)->after('response_time')->index();
            $table->string('risk_level', 20)->default('normal')->after('is_suspicious')->index();
            $table->string('risk_reason')->nullable()->after('risk_level');

            $table->index(['is_suspicious', 'visited_at']);
            $table->index(['risk_level', 'visited_at']);
        });
    }

    public function down(): void
    {
        Schema::table('page_visits', function (Blueprint $table): void {
            $table->dropIndex(['request_host']);
            $table->dropIndex(['is_suspicious']);
            $table->dropIndex(['risk_level']);
            $table->dropIndex(['is_suspicious', 'visited_at']);
            $table->dropIndex(['risk_level', 'visited_at']);
            $table->dropColumn(['request_host', 'is_suspicious', 'risk_level', 'risk_reason']);
        });
    }
};
