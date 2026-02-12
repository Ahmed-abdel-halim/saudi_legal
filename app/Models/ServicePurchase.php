<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServicePurchase extends Model
{
    use HasFactory;

    protected $fillable = [
        'expert_id',
        'client_id',
        'service_id',
        'hours_purchased',
        'hourly_rate',
        'total_price',
        'status', // pending, accepted, rejected, completed, cancelled
        'service_status', // awaiting_start, in_progress, etc.
        'accepted_at',
        'started_at',
        'finished_at',
        'completed_at',
        'resolution_note',
        'resolved_by',
        'resolved_at',
    ];

    protected $casts = [
        'accepted_at' => 'datetime',
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
        'completed_at' => 'datetime',
        'resolved_at' => 'datetime',
    ];

    // Relationships
    public function conversation()
    {
        return $this->hasOne(Conversation::class, 'contract_id')
                    ->where('contract_type', Conversation::TYPE_HOURLY_PURCHASE);
    }

    public function expert()
    {
        return $this->belongsTo(User::class, 'expert_id');
    }

    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function service()
    {
        return $this->belongsTo(ExpertService::class, 'service_id', 'service_id');
    }

    // Scopes
    public function scopeForExpert($query, $expertId)
    {
        return $query->where('expert_id', $expertId);
    }

    public function scopeForClient($query, $clientId)
    {
        return $query->where('client_id', $clientId);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeAccepted($query)
    {
        return $query->where('status', 'accepted');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    // Helper Methods
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isAccepted(): bool
    {
        return $this->status === 'accepted';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Get formatted notification data for this purchase
     */
    public function getNotificationData(): array
    {
        return [
            'id' => $this->id,
            'title' => 'New Request: ' . (optional($this->service)->title ?? 'Service'),
            'message' => 'New request from ' . (optional($this->client)->name ?? 'Client') . ' for ' . $this->hours_purchased . ' hours.',
            'url' => route('dashboard.expert'),
            'client_name' => optional($this->client)->full_name ?? optional($this->client)->name ?? 'Client',
            'client_avatar' => optional($this->client)->avatar_path ?? null,
            'service_title' => optional($this->service)->title ?? 'Service',
            'hours' => $this->hours_purchased,
            'request_id' => $this->id,
            'created_at_human' => $this->created_at ? $this->created_at->diffForHumans() : 'Just now',
        ];
    }
}
