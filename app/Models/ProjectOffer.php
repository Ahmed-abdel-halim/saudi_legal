<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectOffer extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'expert_id',
        'price',
        'delivery_time_days',
        'message',
        'status', // pending, accepted, rejected, withdrawn
        'service_status', // awaiting_start, in_progress, etc.
        'started_at',
        'finished_at',
        'completed_at',
        'accepted_at',
        'resolution_note',
        'resolved_by',
        'resolved_at',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
        'completed_at' => 'datetime',
        'accepted_at' => 'datetime',
        'resolved_at' => 'datetime',
    ];

    public function conversation()
    {
        return $this->hasOne(Conversation::class, 'contract_id')
                    ->where('contract_type', Conversation::TYPE_OFFER);
    }

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id', 'project_id');
    }

    public function expert()
    {
        return $this->belongsTo(User::class, 'expert_id');
    }
}
