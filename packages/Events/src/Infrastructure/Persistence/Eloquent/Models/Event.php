<?php

namespace CodeGarage\Events\Infrastructure\Persistence\Eloquent\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Event extends Model
{
    protected $fillable = [
        'created_by',
        'title',
        'slug',
        'type',
        'summary',
        'description',
        'location',
        'is_online',
        'meeting_url',
        'starts_at',
        'ends_at',
        'capacity',
        'status',
        'published_at',
        'feedback_notes',
        'internal_notes',
        'closed_at',
    ];

    protected $casts = [
        'is_online' => 'boolean',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'published_at' => 'datetime',
        'closed_at' => 'datetime',
        'capacity' => 'integer',
    ];

    protected static function booted(): void
    {
        static::saving(function (Event $event) {
            if (empty($event->slug)) {
                $event->slug = Str::slug($event->title);
            }

            if ($event->status === 'published' && $event->published_at === null) {
                $event->published_at = now();
            }

            if ($event->status === 'closed' && $event->closed_at === null) {
                $event->closed_at = now();
            }
        });
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published');
    }

    public function scopeUpcoming(Builder $query): Builder
    {
        return $query->where('starts_at', '>=', now()->startOfDay());
    }

    public function typeLabel(): string
    {
        return match ($this->type) {
            'graduation' => 'Graduation',
            'project_day' => 'Project day',
            'workshop' => 'Workshop',
            default => 'Coding day',
        };
    }
}
