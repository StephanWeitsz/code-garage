<?php

namespace CodeGarage\DevelopmentRequests\Infrastructure\Persistence\Eloquent\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DevelopmentRequest extends Model
{
    protected $fillable = [
        'user_id',
        'client_name',
        'client_email',
        'client_phone',
        'company_name',
        'preferred_contact_method',
        'project_name',
        'project_type',
        'project_goal',
        'target_users',
        'current_process',
        'must_have_features',
        'nice_to_have_features',
        'integrations',
        'content_and_data',
        'timeline',
        'budget_range',
        'success_measure',
        'additional_context',
        'status',
        'quote_status',
        'quote_currency',
        'quote_amount_min',
        'quote_amount_max',
        'costing_notes',
        'internal_notes',
        'admin_response',
        'contacted_at',
        'quoted_at',
    ];

    protected $casts = [
        'must_have_features' => 'array',
        'nice_to_have_features' => 'array',
        'quote_amount_min' => 'decimal:2',
        'quote_amount_max' => 'decimal:2',
        'contacted_at' => 'datetime',
        'quoted_at' => 'datetime',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
