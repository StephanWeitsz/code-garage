<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->text('feedback_notes')->nullable()->after('published_at');
            $table->text('internal_notes')->nullable()->after('feedback_notes');
            $table->timestamp('closed_at')->nullable()->after('internal_notes');
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn([
                'feedback_notes',
                'internal_notes',
                'closed_at',
            ]);
        });
    }
};
