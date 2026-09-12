<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FatwaQaPair extends Model
{
    protected $table = 'sharia_qa_pairs';

    protected $fillable = [
        'sharia_record_id',
        'qa_id',
        'question',
        'generated_answer',
        'corrected_answer',
        'review_status',
        'traffic_light',
        'reviewer_id',
        'reviewed_at',
        'time_spent',
        'has_custom_citations',
    ];

    protected $casts = [
        'reviewed_at'          => 'datetime',
        'has_custom_citations' => 'boolean',
    ];

    // ── Relations ──────────────────────────────────────────

    public function record(): BelongsTo
    {
        return $this->belongsTo(ShariaRecord::class, 'sharia_record_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    public function citations(): HasMany
    {
        return $this->hasMany(ShariaCitation::class, 'sharia_qa_pair_id');
    }

    // ── Scopes ─────────────────────────────────────────────

    public function scopeApproved($query)
    {
        return $query->where('review_status', 'Approved');
    }

    public function scopePending($query)
    {
        return $query->where('review_status', 'Pending');
    }

    public function scopeGreenLight($query)
    {
        return $query->where('traffic_light', 'green');
    }

    // ── Helpers ────────────────────────────────────────────

    /**
     * Get the final authoritative answer: corrected if modified, otherwise generated.
     */
    public function getFinalAnswerAttribute(): string
    {
        return (!empty($this->corrected_answer))
            ? $this->corrected_answer
            : ($this->generated_answer ?? '');
    }

    /**
     * Export to Unified Polymorphic JSON format.
     */
    public function toPolymorphicArray(): array
    {
        return [
            'qa_id'            => $this->qa_id,
            'question'         => $this->question,
            'answer'           => $this->final_answer,
            'human_review'     => [
                'status'        => $this->review_status,
                'traffic_light' => $this->traffic_light === 'green' ? '🟢' : ($this->traffic_light === 'yellow' ? '🟡' : '🔴'),
                'reviewer_id'   => $this->reviewer_id ? "SCHOLAR-{$this->reviewer_id}" : null,
                'reviewed_at'   => $this->reviewed_at?->toIso8601String(),
            ],
            'citations'        => $this->citations->map(fn($c) => $c->toCitationArray())->values()->all(),
        ];
    }
}
