<?php

namespace CodeGarage\Assignments\Infrastructure\Persistence\Eloquent\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use CodeGarage\Courses\Infrastructure\Persistence\Eloquent\Models\Course;
use CodeGarage\Lessons\Infrastructure\Persistence\Eloquent\Models\Lesson;

class Assignment extends Model
{
    protected $fillable = [
        'course_id',
        'lesson_id',
        'author_id',
        'title',
        'instructions',
        'due_at',
        'due_days_after_completion',
        'requires_completion_before_lesson_complete',
        'max_points',
        'is_published',
    ];

    protected $casts = [
        'due_at' => 'datetime',
        'due_days_after_completion' => 'integer',
        'requires_completion_before_lesson_complete' => 'boolean',
        'is_published' => 'boolean',
    ];

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class, 'lesson_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(AssignmentSubmission::class)->orderByDesc('submitted_at');
    }
}
