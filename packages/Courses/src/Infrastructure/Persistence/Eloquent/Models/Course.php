<?php

namespace CodeGarage\Courses\Infrastructure\Persistence\Eloquent\Models;

use App\Enums\CourseStatus;
use App\Enums\DifficultyLevel;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use CodeGarage\Lessons\Infrastructure\Persistence\Eloquent\Models\CourseSection;
use CodeGarage\Lessons\Infrastructure\Persistence\Eloquent\Models\Lesson;
use CodeGarage\Queries\Infrastructure\Persistence\Eloquent\Models\CourseQuery;

class Course extends Model
{
    protected $fillable = [
        'lecturer_id',
        'title',
        'slug',
        'description',
        'cover_image',
        'knowledge_prerequisites',
        'equipment_requirements',
        'difficulty_level',
        'category',
        'status',
        'pricing_type',
        'pricing_amount',
        'pricing_currency',
        'default_meeting_url',
        'published_at',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'knowledge_prerequisites' => 'array',
        'equipment_requirements' => 'array',
        'pricing_amount' => 'decimal:2',
        'difficulty_level' => DifficultyLevel::class,
        'status' => CourseStatus::class,
    ];

    protected static function booted(): void
    {
        static::saving(function (Course $course) {
            if (empty($course->slug)) {
                $course->slug = Str::slug($course->title);
            }
        });
    }

    public function lecturer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'lecturer_id');
    }

    public function sections(): HasMany
    {
        return $this->hasMany(CourseSection::class)->orderBy('sequence');
    }

    public function lessons(): HasMany
    {
        return $this->hasMany(Lesson::class)->orderBy('sequence');
    }

    public function courseQueries(): HasMany
    {
        return $this->hasMany(CourseQuery::class)->latest();
    }

    protected function coverImage(): Attribute
    {
        return Attribute::make(
            set: function ($value) {
                if (! is_string($value) || $value === '') {
                    return $value;
                }

                $normalized = ltrim($value, '/');

                if (str_starts_with($normalized, 'storage/')) {
                    return substr($normalized, strlen('storage/'));
                }

                if (str_starts_with($value, 'http://') || str_starts_with($value, 'https://')) {
                    $path = (string) parse_url($value, PHP_URL_PATH);
                    $path = ltrim($path, '/');

                    if (str_starts_with($path, 'storage/')) {
                        return substr($path, strlen('storage/'));
                    }
                }

                return $normalized;
            }
        );
    }
}
