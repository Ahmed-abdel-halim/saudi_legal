<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LegalRecord extends Model
{
    protected $fillable = [
        'record_id',
        'domain',
        'sub_domain',
        'language',
        'upload_date',
        'tags',
        'source_type',
        'source_reference',
        'court_type',
        'full_text',
        'case_summary',
    ];

    protected $casts = [
        'tags'        => 'array',
        'upload_date' => 'date',
    ];

    // ── Relations ──────────────────────────────────────────

    public function citations(): HasMany
    {
        return $this->hasMany(LegalCitation::class);
    }

    public function qaPairs(): HasMany
    {
        return $this->hasMany(LegalQaPair::class);
    }

    // ── Scopes ─────────────────────────────────────────────

    public function scopeDomain($query, string $domain)
    {
        return $query->where('domain', $domain);
    }

    public function scopeSubDomain($query, string $sub)
    {
        return $query->where('sub_domain', $sub);
    }
}
