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
    ];

    protected $casts = [
        'errors' => 'array',
        'is_correct' => 'boolean',
        'assigned_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function expert()
    {
        return $this->belongsTo(User::class, 'expert_id');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeForExpert($query, $expertId)
    {
        return $query->where('expert_id', $expertId);
    }
    
    public function scopeByDomain($query, $domain)
    {
        return $query->where('domain', $domain);
    }

    public function scopeSentiment($query)
    {
        return $query->where('task_type', 'sentiment');
    }
}
