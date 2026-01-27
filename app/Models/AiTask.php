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
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
        'completed_at' => 'datetime',
    ];
}
