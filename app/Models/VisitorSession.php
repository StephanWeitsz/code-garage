<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VisitorSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id',
        'user_id',
        'ip_address',
        'user_agent',
        'referrer',
        'device_type',
        'browser',
        'platform',
        'country',
        'city',
        'first_seen_at',
        'last_seen_at',
    ];

    protected $casts = [
        'first_seen_at' => 'datetime',
        'last_seen_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function pageVisits(): HasMany
    {
        return $this->hasMany(PageVisit::class);
    }

    public function courseViews(): HasMany
    {
        return $this->hasMany(CourseView::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('last_seen_at', '>=', now()->subMinutes(config('analytics.active_window_minutes', 5)));
    }
}
