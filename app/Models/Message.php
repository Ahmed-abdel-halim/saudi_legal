<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;

    protected $fillable = [
        'conversation_id',
        'sender_id',
        'sender_type',
        'content',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    // Sender type constants
    const TYPE_COMPANY = 'company';
    const TYPE_EXPERT = 'expert';
    const TYPE_SYSTEM = 'system';

    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }

    // Sender can be null for system messages
    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function isSystemMessage(): bool
    {
        return $this->sender_type === self::TYPE_SYSTEM;
    }
}
