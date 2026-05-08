<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lessons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained('courses')->cascadeOnDelete();
            $table->string('title');
            $table->string('slug');
            $table->longText('content');
            $table->string('content_type', 32)->default('text');
            $table->unsignedInteger('sequence');
            $table->boolean('is_preview')->default(false);
            $table->timestamps();

            $table->unique(['course_id', 'slug']);
            $table->unique(['course_id', 'sequence']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lessons');
    }
};
