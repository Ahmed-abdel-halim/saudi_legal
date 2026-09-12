<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ShariaRecord extends Model
{
    protected $fillable = [
        'record_id',
        'domain',
        'sub_domain',
        'source_authority',
        'verification_status',
        'title',
        'full_text',
        'summary',
        'core_principles',
        'tags',
        'source_url',
    ];

    protected $casts = [
        'core_principles' => 'array',
        'tags'            => 'array',
    ];

    // ── Relations ──────────────────────────────────────────

    public function citations(): HasMany
    {
        return $this->hasMany(ShariaCitation::class, 'sharia_record_id');
    }

    public function qaPairs(): HasMany
    {
        return $this->hasMany(FatwaQaPair::class, 'sharia_record_id');
    }

    // ── Scopes ─────────────────────────────────────────────

    public function scopeVerified($query)
    {
        return $query->where('verification_status', 'VERIFIED');
    }

    public function scopeSubDomain($query, string $subDomain)
    {
        return $query->where('sub_domain', $subDomain);
    }

    public function scopeAuthority($query, string $authority)
    {
        return $query->where('source_authority', $authority);
    }
}
