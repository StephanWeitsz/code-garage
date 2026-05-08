<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('assignments', function (Blueprint $table) {
            $table->unsignedSmallInteger('due_days_after_completion')->nullable()->after('due_at');
        });

        Schema::table('assignment_submissions', function (Blueprint $table) {
            $table->dateTime('due_at')->nullable()->after('submitted_at');
        });
    }

    public function down(): void
    {
        Schema::table('assignment_submissions', function (Blueprint $table) {
            $table->dropColumn('due_at');
        });

        Schema::table('assignments', function (Blueprint $table) {
            $table->dropColumn('due_days_after_completion');
        });
    }
};
