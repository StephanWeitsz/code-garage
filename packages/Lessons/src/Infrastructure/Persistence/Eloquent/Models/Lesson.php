<?php

namespace CodeGarage\Lessons\Infrastructure\Persistence\Eloquent\Models;

use App\Enums\LessonContentType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use CodeGarage\Courses\Infrastructure\Persistence\Eloquent\Models\Course;

class Lesson extends Model
{
    protected $fillable = [
        'course_id',
        'course_section_id',
        'title',
        'slug',
        'content',
        'content_type',
        'sequence',
        'is_preview',
    ];

    protected $casts = [
        'is_preview' => 'boolean',
        'content_type' => LessonContentType::class,
    ];

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(CourseSection::class, 'course_section_id');
    }
}
