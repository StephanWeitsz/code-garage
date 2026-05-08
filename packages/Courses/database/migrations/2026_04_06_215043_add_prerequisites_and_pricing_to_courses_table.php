<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->json('knowledge_prerequisites')->nullable()->after('description');
            $table->json('equipment_requirements')->nullable()->after('knowledge_prerequisites');
            $table->string('pricing_type', 32)->default('free')->after('status');
            $table->decimal('pricing_amount', 10, 2)->nullable()->after('pricing_type');
            $table->string('pricing_currency', 3)->default('ZAR')->after('pricing_amount');
        });
    }

    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropColumn([
                'knowledge_prerequisites',
                'equipment_requirements',
                'pricing_type',
                'pricing_amount',
                'pricing_currency',
            ]);
        });
    }
};
