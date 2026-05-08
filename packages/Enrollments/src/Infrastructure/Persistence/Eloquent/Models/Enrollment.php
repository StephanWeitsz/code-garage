<?php

namespace CodeGarage\Enrollments\Infrastructure\Persistence\Eloquent\Models;

use App\Enums\EnrollmentStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use CodeGarage\Courses\Infrastructure\Persistence\Eloquent\Models\Course;

class Enrollment extends Model
{
    protected $fillable = [
        'user_id',
        'course_id',
        'status',
        'meeting_url',
        'enrolled_at',
    ];

    protected $casts = [
        'enrolled_at' => 'datetime',
        'status' => EnrollmentStatus::class,
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }
}
