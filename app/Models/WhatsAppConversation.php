<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WhatsAppConversation extends Model
{
    protected $table = 'whatsapp_conversations';

    protected $fillable = [
        'phone_number',
        'display_name',
        'session_state',
        'message_count',
        'free_limit',
        'last_active_at',
        'inactivity_warned_at',
    ];

    protected $casts = [
        'last_active_at'       => 'datetime',
        'inactivity_warned_at' => 'datetime',
    ];

    public function messages(): HasMany
    {
        return $this->hasMany(WhatsAppMessage::class, 'whatsapp_conversation_id');
    }

    /**
     * هل المستخدم وصل لحد الرسائل المجانية؟
     */
    public function hasReachedLimit(): bool
    {
        return $this->message_count >= $this->free_limit;
    }

    /**
     * زيادة عداد الرسائل وتحديث وقت آخر نشاط وإعادة تصفير التنبيه
     */
    public function incrementAndTouch(): void
    {
        $this->increment('message_count');
        $this->update([
            'last_active_at'       => now(),
            'inactivity_warned_at' => null,
        ]);
    }

    /**
     * تحديث وقت النشاط وتصفير التنبيه دون زيادة العداد
     */
    public function touchActivity(): void
    {
        $this->update([
            'last_active_at'       => now(),
            'inactivity_warned_at' => null,
        ]);
    }

    /**
     * الحصول على آخر N رسائل للسياق
     */
    public function getRecentHistory(int $count = 6)
    {
        return $this->messages()
            ->orderBy('created_at', 'desc')
            ->take($count)
            ->get()
            ->reverse()
            ->values();
    }
}
