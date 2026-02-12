<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    use HasFactory;

    protected $fillable = [
        'contract_type',
        'contract_id',
        'participant_1',
        'participant_2',
        'status',
        'company_last_read_at',
        'expert_last_read_at',
    ];

    protected $casts = [
        'company_last_read_at' => 'datetime',
        'expert_last_read_at' => 'datetime',
    ];

    // Status constants
    const STATUS_PENDING = 'pending';
    const STATUS_ACTIVE = 'active';
    const STATUS_CLOSED = 'closed';
    const STATUS_ARCHIVED = 'archived';

    // Contract type constants
    const TYPE_OFFER = 'offer';
    const TYPE_HOURLY_PURCHASE = 'hourly_purchase';

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function lastMessage()
    {
        return $this->hasOne(Message::class)->latest();
    }

    public function participant1()
    {
        return $this->belongsTo(User::class, 'participant_1');
    }

    public function participant2()
    {
        return $this->belongsTo(User::class, 'participant_2');
    }

    // Polymorphic relationship to contract
    public function contract()
    {
        return $this->contract_type === self::TYPE_OFFER
            ? $this->belongsTo(ProjectOffer::class, 'contract_id')
            : $this->belongsTo(ServicePurchase::class, 'contract_id');
    }

    // Helper methods
    public function isParticipant($userId): bool
    {
        return $this->participant_1 == $userId || $this->participant_2 == $userId;
    }

    public function getOtherParticipant($userId)
    {
        return $this->participant_1 == $userId 
            ? $this->participant_2 
            : $this->participant_1;
    }

    public function unreadCountFor($userId): int
    {
        $lastReadAt = $this->participant_1 == $userId 
            ? $this->company_last_read_at 
            : $this->expert_last_read_at;
        
        return $this->messages()
            ->where('sender_id', '!=', $userId)
            ->where('created_at', '>', $lastReadAt ?? '1970-01-01')
            ->count();
    }

    public function markAsReadBy($userId): void
    {
        $field = $this->participant_1 == $userId 
            ? 'company_last_read_at' 
            : 'expert_last_read_at';
        
        $this->update([$field => now()]);
    }
}
