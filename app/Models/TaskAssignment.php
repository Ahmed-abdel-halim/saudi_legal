<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'task_id',
        'expert_id',
        'assigned_at',
        'expires_at'
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function task()
    {
        return $this->belongsTo(AiTask::class, 'task_id');
    }

    public function expert()
    {
        return $this->belongsTo(User::class, 'expert_id');
    }

    /**
     * Scope to find active assignments
     */
    public function scopeActive($query)
    {
        return $query->where('expires_at', '>', now());
    }
}
