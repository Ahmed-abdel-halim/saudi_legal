<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AiTask extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'ai_tasks_v2';

    protected $fillable = [
        'task_type',
        'original_data',
        'ai_suggestion',
        'status',
        'payment_status',
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
        'status' => \App\Enums\TaskStatus::class ,
        'sentiment' => \App\Enums\TaskSentiment::class ,
    ];

    public function responses()
    {
        return $this->hasMany(AiResponse::class , 'task_id');
    }

    public function assignments()
    {
        return $this->hasMany(TaskAssignment::class , 'task_id');
    }

    public function consensus()
    {
        return $this->hasOne(TaskConsensus::class , 'task_id');
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class , 'category_ai_task', 'ai_task_id', 'category_id');
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class , 'ai_task_tag', 'ai_task_id', 'tag_id');
    }
}
