<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PageVisit extends Model
{
    use HasFactory;

    protected $fillable = [
        'visitor_session_id',
        'user_id',
        'url',
        'route_name',
        'page_title',
        'method',
        'visited_at',
        'response_time',
    ];

    protected $casts = [
        'visited_at' => 'datetime',
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
