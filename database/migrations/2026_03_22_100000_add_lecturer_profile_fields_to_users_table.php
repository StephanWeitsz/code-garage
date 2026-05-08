<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('lecturer_headline')->nullable()->after('status');
            $table->text('lecturer_bio')->nullable()->after('lecturer_headline');
            $table->string('lecturer_specialties')->nullable()->after('lecturer_bio');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['lecturer_headline', 'lecturer_bio', 'lecturer_specialties']);
        });
    }
};
