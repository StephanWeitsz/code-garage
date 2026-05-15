<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->string('image_path')->nullable()->after('body');
            $table->string('cta_label')->nullable()->after('image_path');
            $table->string('cta_url')->nullable()->after('cta_label');
            $table->timestamp('starts_at')->nullable()->after('status');
            $table->timestamp('ends_at')->nullable()->after('starts_at');

            $table->index(['type', 'status', 'starts_at', 'ends_at']);
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropIndex(['type', 'status', 'starts_at', 'ends_at']);
            $table->dropColumn(['image_path', 'cta_label', 'cta_url', 'starts_at', 'ends_at']);
        });
    }
};
