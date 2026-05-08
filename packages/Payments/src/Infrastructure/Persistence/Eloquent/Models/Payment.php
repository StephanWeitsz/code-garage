<?php

namespace CodeGarage\Payments\Infrastructure\Persistence\Eloquent\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use CodeGarage\Courses\Infrastructure\Persistence\Eloquent\Models\Course;

class Payment extends Model
{
    protected $fillable = [
        'user_id',
        'course_id',
        'channel',
        'status',
        'amount',
        'currency',
        'reference',
        'external_reference',
        'payer_name',
        'transfer_reference',
        'paid_at',
        'verified_at',
        'verified_by',
        'notes',
        'metadata',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
        'verified_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}
