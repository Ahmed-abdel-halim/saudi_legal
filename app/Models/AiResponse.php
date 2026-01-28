<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AiResponse extends Model
{
    use HasFactory;

    protected $table = 'ai_responses_v2';

    protected $fillable = [
        'task_id',
        'expert_id',
        'corrected_data',
        'correction_notes',
        'confidence_level',
        'action',
        'reward_amount',
    ];

    protected $casts = [
        'confidence_level' => 'integer',
        'reward_amount' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function task()
    {
        return $this->belongsTo(AiTask::class, 'task_id');
    }

    public function expert()
    {
        return $this->belongsTo(User::class, 'expert_id');
    }
}
