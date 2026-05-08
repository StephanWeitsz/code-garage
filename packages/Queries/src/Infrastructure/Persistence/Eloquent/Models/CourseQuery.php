<?php

namespace CodeGarage\Queries\Infrastructure\Persistence\Eloquent\Models;

use App\Models\User;
use CodeGarage\Courses\Infrastructure\Persistence\Eloquent\Models\Course;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CourseQuery extends Model
{
    protected $fillable = [
        'course_id',
        'user_id',
        'name',
        'email',
        'mobile',
        'subject',
        'message',
        'audience',
        'status',
        'resolved_at',
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
    ];

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
