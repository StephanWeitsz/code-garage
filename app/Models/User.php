<?php

namespace App\Models;

use App\Enums\UserStatus;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use CodeGarage\Courses\Infrastructure\Persistence\Eloquent\Models\Course;
use CodeGarage\Queries\Infrastructure\Persistence\Eloquent\Models\CourseQuery;

class User extends Authenticatable implements FilamentUser
{
    use HasApiTokens;
    use HasFactory;
    use HasProfilePhoto;
    use HasRoles;
    use Notifiable;
    use TwoFactorAuthenticatable;

    protected $fillable = [
        'name',
        'email',
        'mobile',
        'password',
        'status',
        'lecturer_headline',
        'lecturer_bio',
        'lecturer_specialties',
        'is_featured_lecturer',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'status' => UserStatus::class,
        'password' => 'hashed',
        'is_featured_lecturer' => 'boolean',
    ];

    protected $appends = [
        'profile_photo_url',
    ];

    protected static function booted(): void
    {
        static::saved(function (User $user) {
            if ($user->is_featured_lecturer) {
                static::query()
                    ->whereKeyNot($user->getKey())
                    ->where('is_featured_lecturer', true)
                    ->update(['is_featured_lecturer' => false]);
            }
        });
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return match ($panel->getId()) {
            'admin' => $this->hasRole('admin'),
            'lecturer' => $this->hasAnyRole(['admin', 'lecturer']),
            default => false,
        };
    }

    public function taughtCourses(): HasMany
    {
        return $this->hasMany(Course::class, 'lecturer_id');
    }

    public function courseQueries(): HasMany
    {
        return $this->hasMany(CourseQuery::class);
    }

    public function lecturerSpecialtiesList(): array
    {
        return collect(explode(',', (string) $this->lecturer_specialties))
            ->map(fn (string $specialty) => trim($specialty))
            ->filter()
            ->values()
            ->all();
    }
}
