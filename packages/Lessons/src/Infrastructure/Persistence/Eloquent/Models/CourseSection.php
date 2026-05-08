<?php

namespace CodeGarage\Lessons\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use CodeGarage\Courses\Infrastructure\Persistence\Eloquent\Models\Course;

class CourseSection extends Model
{
    protected $fillable = [
        'course_id',
        'title',
        'slug',
        'description',
        'sequence',
    ];

    protected static function booted(): void
    {
        static::saving(function (CourseSection $section) {
            if (empty($section->slug)) {
                $section->slug = Str::slug($section->title);
            }
        });
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function lessons(): HasMany
    {
        return $this->hasMany(Lesson::class, 'course_section_id')->orderBy('sequence');
    }
}
