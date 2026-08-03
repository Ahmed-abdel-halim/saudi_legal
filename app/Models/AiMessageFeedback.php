<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AiMessageFeedback extends Model
{
    use HasFactory;

    protected $table = 'ai_message_feedbacks';

    protected $fillable = [
        'user_id',
        'ai_conversation_id',
        'ai_message_id',
        'rating',
        'reason',
        'user_query',
        'ai_response',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function conversation()
    {
        return $this->belongsTo(AiConversation::class, 'ai_conversation_id');
    }

    public function message()
    {
        return $this->belongsTo(AiMessage::class, 'ai_message_id');
    }
}
