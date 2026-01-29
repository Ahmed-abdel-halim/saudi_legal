<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GovernanceLog extends Model
{
    protected $fillable = [
        'expert_id',
        'task_id',
        'event_type',
        'event_data',
        'trust_score_before',
        'trust_score_after',
    ];

    protected $casts = [
        'event_data' => 'array',
        'trust_score_before' => 'decimal:2',
        'trust_score_after' => 'decimal:2',
    ];

    public function expert(): BelongsTo
    {
        return $this->belongsTo(User::class, 'expert_id');
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(AiTask::class, 'task_id');
    }
}
