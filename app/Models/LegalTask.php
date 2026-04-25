<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LegalTask extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'task_type',
        'expert_id',
        'status',
        'assigned_at',
        'completed_at',
        'question',
        'proposed_answer',
        'correct_answer',
        'law_article_text',
        'law_article_number',
        'law_system_name',
        'case_reference',
        'case_text',
        'is_correct',
        'expert_comment',
        'domain',
        'source_file',
        'row_number'
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
        'completed_at' => 'datetime',
        'is_correct' => 'boolean',
    ];

    /**
     * العلاقة مع الخبير (المحامي)
     */
    public function expert()
    {
        return $this->belongsTo(User::class, 'expert_id');
    }

    /**
     * Scope للبحث عن المهام المكتملة
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope للبحث عن المهام حسب النوع
     */
    public function scopeByType($query, $type)
    {
        return $query->where('task_type', $type);
    }
}
