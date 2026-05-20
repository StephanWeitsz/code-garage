<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CourseView extends Model
{
    use HasFactory;

    public const EVENT_VIEW = 'view';
    public const EVENT_ENROLL_CLICK = 'enroll_click';
    public const EVENT_REGISTRATION_CONVERSION = 'registration_conversion';

    protected $fillable = [
        'visitor_session_id',
        'user_id',
        'course_id',
        'course_slug',
        'course_title',
        'event_type',
        'occurred_at',
    ];

    protected $casts = [
        'occurred_at' => 'datetime',
    ];

    public function visitorSession(): BelongsTo
    {
        return $this->belongsTo(VisitorSession::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
