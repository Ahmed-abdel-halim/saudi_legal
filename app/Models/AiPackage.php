<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AiPackage extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'price',
        'billing_period',
        'query_limit',
        'is_unlimited',
        'features',
        'badge_text',
        'is_popular',
        'is_active',
        'is_free',
        'stripe_price_id',
        'color_scheme',
        'sort_order',
    ];

    protected $casts = [
        'features'     => 'array',
        'is_unlimited' => 'boolean',
        'is_popular'   => 'boolean',
        'is_active'    => 'boolean',
        'is_free'      => 'boolean',
        'price'        => 'decimal:2',
    ];

    // ─── Relationships ─────────────────────────────────────────────────────────

    public function subscriptions()
    {
        return $this->hasMany(AiSubscription::class);
    }

    public function activeSubscriptions()
    {
        return $this->hasMany(AiSubscription::class)->where('status', 'active');
    }

    // ─── Scopes ───────────────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    public function getPriceDisplayAttribute(): string
    {
        if ($this->is_free || $this->price == 0) {
            return 'مجاناً';
        }
        return number_format($this->price, 0) . ' ر.س';
    }

    public function getBillingPeriodLabelAttribute(): string
    {
        return match ($this->billing_period) {
            'monthly'  => '/ شهرياً',
            'yearly'   => '/ سنوياً',
            'lifetime' => 'مدى الحياة',
            default    => '',
        };
    }

    public function getQueryLimitDisplayAttribute(): string
    {
        if ($this->is_unlimited || $this->query_limit === -1) {
            return 'استعلامات غير محدودة';
        }
        return $this->query_limit . ' استعلاماً شهرياً';
    }
}
