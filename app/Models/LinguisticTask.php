<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LinguisticTask extends Model
{
    use HasFactory;

    protected $fillable = [
        'task_type',
        'expert_id',
        'status',
        'assigned_at',
        'completed_at',
        'sentence',
        'correct_sentence',
        'errors',
        'comment_text',
        'proposed_classification',
        'correct_classification',
        'is_correct',
        'domain',
        'csv_file',
        'row_number',
        'time_spent'
    ];

    protected $casts = [
        'errors' => 'array',
        'assigned_at' => 'datetime',
        'completed_at' => 'datetime',
        'is_correct' => 'boolean',
    ];

    public function expert()
    {
        return $this->belongsTo(User::class, 'expert_id');
    }
}
