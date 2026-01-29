<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaskConsensus extends Model
{
    protected $table = 'task_consensus';
    
    protected $fillable = [
        'task_id',
        'expert_answers',
        'final_answer',
        'confidence_level',
        'consensus_type',
        'conflict_notes',
        'resolved_by',
        'resolved_at',
    ];

    protected $casts = [
        'expert_answers' => 'array',
        'final_answer' => 'array',
        'confidence_level' => 'decimal:2',
        'resolved_at' => 'datetime',
    ];

    public function task(): BelongsTo
    {
        return $this->belongsTo(AiTask::class, 'task_id');
    }

    public function resolvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }
}
