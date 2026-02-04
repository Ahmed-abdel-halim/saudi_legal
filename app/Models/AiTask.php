<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AiTask extends Model
{
    use HasFactory;

    protected $table = 'ai_tasks_v2';

    protected $fillable = [
        'task_type',
        'original_data',
        'ai_suggestion',
        'status',
        'assigned_expert_id',
        'assigned_at',
        'completed_at',
        'is_gold_standard',
        'gold_answer',
        'required_responses',
        'current_responses',
        'consensus_status',
        'client_id',
        'task_domain',
        'allowed_roles',
        'allow_all_roles'
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
        'completed_at' => 'datetime',
        'gold_answer' => 'array',
        'allowed_roles' => 'array',
        'allow_all_roles' => 'boolean',
    ];

    public function responses()
    {
        return $this->hasMany(AiResponse::class, 'task_id');
    }

    public function assignments()
    {
        return $this->hasMany(TaskAssignment::class, 'task_id');
    }

    public function consensus()
    {
        return $this->hasOne(TaskConsensus::class, 'task_id');
    }
}
