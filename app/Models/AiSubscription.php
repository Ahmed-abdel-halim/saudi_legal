<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AiSubscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'ai_package_id',
        'status',
        'stripe_session_id',
        'stripe_payment_intent_id',
        'stripe_subscription_id',
        'amount_paid',
        'currency',
        'starts_at',
        'ends_at',
        'queries_used',
        'is_unlimited',
        'granted_by',
        'notes',
    ];

    protected $casts = [
        'starts_at'   => 'datetime',
        'ends_at'     => 'datetime',
        'amount_paid' => 'decimal:2',
        'is_unlimited' => 'boolean',
    ];

    // ─── Relationships ─────────────────────────────────────────────────────────

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function package()
    {
        return $this->belongsTo(AiPackage::class, 'ai_package_id');
    }

    // ─── Scopes ───────────────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('status', 'active')
                     ->where(function ($q) {
                         $q->whereNull('ends_at')
                           ->orWhere('ends_at', '>', now());
                     });
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    public function isActive(): bool
    {
        return $this->status === 'active'
            && ($this->ends_at === null || $this->ends_at->isFuture());
    }

    /**
     * هل الاشتراك غير محدود الاستعلامات فعلياً؟
     * يصح إذا: الحقل is_unlimited مفعّل، أو الباقة نفسها غير محدودة، أو ends_at فارغ.
     */
    public function isEffectivelyUnlimited(): bool
    {
        if ($this->is_unlimited) return true;
        if ($this->ends_at === null && $this->status === 'active') return true;
        if ($this->package && ($this->package->is_unlimited || $this->package->query_limit === -1)) return true;
        return false;
    }

    /**
     * هل الاشتراك مدى الحياة (Lifetime)؟
     */
    public function isLifetime(): bool
    {
        return $this->status === 'active' && $this->ends_at === null;
    }

    public function getRemainingQueriesAttribute(): int
    {
        if ($this->isEffectivelyUnlimited()) return PHP_INT_MAX;
        if (!$this->package) return 0;
        return max(0, $this->package->query_limit - $this->queries_used);
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'active'    => 'نشط',
            'pending'   => 'في الانتظار',
            'cancelled' => 'ملغي',
            'expired'   => 'منتهي',
            default     => $this->status,
        };
    }
}
