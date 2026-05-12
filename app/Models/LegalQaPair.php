<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LegalQaPair extends Model
{
    protected $fillable = [
        'legal_record_id',
        'qa_id',
        'question',
        'generated_answer',
        'review_status',
        'reviewer_id',
        'corrected_answer',
        'reviewed_at',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    // ── Relations ──────────────────────────────────────────

    public function record(): BelongsTo
    {
        return $this->belongsTo(LegalRecord::class, 'legal_record_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    // ── Scopes ─────────────────────────────────────────────

    public function scopePending($query)
    {
        return $query->where('review_status', 'Pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('review_status', 'Approved');
    }

    public function scopeNeedsReview($query)
    {
        return $query->whereIn('review_status', ['Pending', 'Modified']);
    }

    // ── Helpers ────────────────────────────────────────────

    /**
     * The final accepted answer: corrected if Modified, generated otherwise.
     */
    public function getFinalAnswerAttribute(): string
    {
        return ($this->review_status === 'Modified' && $this->corrected_answer)
            ? $this->corrected_answer
            : $this->generated_answer;
    }

    /**
     * Return the structured QA as array (matches the desired JSON format).
     */
    public function toApiArray(): array
    {
        return [
            'qa_id'            => $this->qa_id,
            'question'         => $this->question,
            'generated_answer' => $this->generated_answer,
            'human_review'     => [
                'status'           => $this->review_status,
                'reviewer_id'      => $this->reviewer_id ? "EXPERT-{$this->reviewer_id}" : '',
                'corrected_answer' => $this->corrected_answer ?? '',
            ],
        ];
    }
}
