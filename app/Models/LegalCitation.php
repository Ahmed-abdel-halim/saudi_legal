<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LegalCitation extends Model
{
    protected $fillable = [
        'legal_record_id',
        'system_name',
        'article_number',
        'citation_source',   // 'law' | 'contract' | 'religious' | 'other'
        'legal_article_id',
    ];

    // ── Relations ──────────────────────────────────────────

    public function record(): BelongsTo
    {
        return $this->belongsTo(LegalRecord::class, 'legal_record_id');
    }

    /**
     * The matched LegalArticle row (fetched from legal_articles table).
     * article_text is accessed via: $citation->article->content
     */
    public function article(): BelongsTo
    {
        return $this->belongsTo(LegalArticle::class, 'legal_article_id');
    }

    // ── Accessor: article_text (from legal_articles.content) ──

    public function getArticleTextAttribute(): ?string
    {
        return $this->article?->content;
    }

    /**
     * Return the structured citation as array (matches the desired JSON format).
     */
    // ── Helpers ────────────────────────────────────────────

    /** True when this citation comes from an official Saudi law system */
    public function isLaw(): bool
    {
        return $this->citation_source === 'law';
    }

    /** True when this citation comes from a contract/agreement clause */
    public function isContract(): bool
    {
        return $this->citation_source === 'contract';
    }

    /** True when this citation comes from a religious source (Quran/Hadith) */
    public function isReligious(): bool
    {
        return $this->citation_source === 'religious';
    }

    public function toApiArray(): array
    {
        return [
            'system_name'    => $this->system_name,
            'article_number' => $this->article_number,
            'article_text'   => $this->getArticleTextAttribute() ?? '',
        ];
    }
}
