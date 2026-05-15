<?php

namespace CodeGarage\Posts\Infrastructure\Persistence\Eloquent\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use CodeGarage\Courses\Infrastructure\Persistence\Eloquent\Models\Course;
use CodeGarage\Lessons\Infrastructure\Persistence\Eloquent\Models\Lesson;

class Post extends Model
{
    protected $fillable = [
        'course_id',
        'lesson_id',
        'author_id',
        'title',
        'body',
        'image_path',
        'cta_label',
        'cta_url',
        'type',
        'status',
        'starts_at',
        'ends_at',
        'is_pinned',
    ];

    protected $casts = [
        'is_pinned' => 'boolean',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class, 'course_id');
    }

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class, 'lesson_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(PostReply::class)->orderBy('created_at');
    }

    public function scopeVisibleToPublic(Builder $query): Builder
    {
        return $query
            ->where('type', 'ad')
            ->where('status', 'active')
            ->where(function (Builder $inner) {
                $inner->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })
            ->where(function (Builder $inner) {
                $inner->whereNull('ends_at')->orWhere('ends_at', '>=', now());
            });
    }

    public function isAd(): bool
    {
        return $this->type === 'ad';
    }

    public function imageUrl(): ?string
    {
        return $this->image_path ? '/storage/'.ltrim($this->image_path, '/') : null;
    }
}
