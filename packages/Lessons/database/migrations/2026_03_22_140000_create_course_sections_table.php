<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('course_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained('courses')->cascadeOnDelete();
            $table->string('title');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->unsignedInteger('sequence');
            $table->timestamps();

            $table->unique(['course_id', 'slug']);
            $table->unique(['course_id', 'sequence']);
        });

        Schema::table('lessons', function (Blueprint $table) {
            $table->foreignId('course_section_id')
                ->nullable()
                ->after('course_id')
                ->constrained('course_sections')
                ->cascadeOnDelete();
        });

        $courseIds = DB::table('courses')->pluck('id');

        foreach ($courseIds as $courseId) {
            $sectionId = DB::table('course_sections')->insertGetId([
                'course_id' => $courseId,
                'title' => 'Course Overview',
                'slug' => Str::slug('Course Overview'),
                'description' => 'Imported from the original flat course structure.',
                'sequence' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('lessons')
                ->where('course_id', $courseId)
                ->update(['course_section_id' => $sectionId]);
        }

        Schema::table('lessons', function (Blueprint $table) {
            $table->dropUnique('lessons_course_id_sequence_unique');
            $table->unique(['course_section_id', 'sequence']);
        });
    }

    public function down(): void
    {
        Schema::table('lessons', function (Blueprint $table) {
            $table->dropUnique('lessons_course_section_id_sequence_unique');
            $table->unique(['course_id', 'sequence']);
            $table->dropConstrainedForeignId('course_section_id');
        });

        Schema::dropIfExists('course_sections');
    }
};
