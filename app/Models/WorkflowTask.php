<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class WorkflowTask extends Model
{
    use HasFactory;

    protected $table = 'workflow_tasks';
    protected $primaryKey = 'task_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'task_id',
        'task_type',
        'status_code',
        'confidence_score',
        'hospital_id',
        'insurance_id',
        'payload',
        'original_payload',
        'audit_trail',
        'assigned_doctor_id',
        'doctor_response',
        'doctor_comment',
        'reward_amount',
        'doctor_completed_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'original_payload' => 'array',
        'audit_trail' => 'array',
        'doctor_completed_at' => 'datetime',
        'confidence_score' => 'float',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->task_id)) {
                $model->task_id = (string) Str::uuid();
            }
        });
    }

    /**
     * Get the doctor assigned to audit this task.
     */
    public function doctor()
    {
        return $this->belongsTo(User::class, 'assigned_doctor_id');
    }

    /**
     * State checking helpers
     */
    public function isGreen(): bool
    {
        return $this->status_code === 1;
    }

    public function isYellow(): bool
    {
        return $this->status_code === 2;
    }

    public function isRed(): bool
    {
        return $this->status_code === 3;
    }
}
