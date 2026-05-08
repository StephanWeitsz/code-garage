<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('development_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('client_name');
            $table->string('client_email');
            $table->string('client_phone')->nullable();
            $table->string('company_name')->nullable();
            $table->string('preferred_contact_method')->default('email');
            $table->string('project_name');
            $table->string('project_type');
            $table->text('project_goal');
            $table->text('target_users')->nullable();
            $table->text('current_process')->nullable();
            $table->json('must_have_features')->nullable();
            $table->json('nice_to_have_features')->nullable();
            $table->text('integrations')->nullable();
            $table->text('content_and_data')->nullable();
            $table->string('timeline')->nullable();
            $table->string('budget_range')->nullable();
            $table->text('success_measure')->nullable();
            $table->text('additional_context')->nullable();
            $table->string('status')->default('new');
            $table->string('quote_status')->default('not_started');
            $table->string('quote_currency', 3)->default('ZAR');
            $table->decimal('quote_amount_min', 12, 2)->nullable();
            $table->decimal('quote_amount_max', 12, 2)->nullable();
            $table->text('costing_notes')->nullable();
            $table->text('internal_notes')->nullable();
            $table->text('admin_response')->nullable();
            $table->timestamp('contacted_at')->nullable();
            $table->timestamp('quoted_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'quote_status']);
            $table->index('client_email');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('development_requests');
    }
};
