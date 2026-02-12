<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'contract_type',
        'contract_id',
        'company_id',
        'expert_id',
        'rating',
        'communication_rating',
        'quality_rating',
        'delivery_time_rating',
        'comment',
        'is_edited',
        'edited_at',
    ];

    protected $casts = [
        'is_edited' => 'boolean',
        'edited_at' => 'datetime',
        'rating' => 'integer',
        'communication_rating' => 'integer',
        'quality_rating' => 'integer',
        'delivery_time_rating' => 'integer',
    ];

    const EDIT_WINDOW_HOURS = 24;

    public function canEdit(): bool
    {
        return !$this->is_edited && 
               $this->created_at->diffInHours(now()) < self::EDIT_WINDOW_HOURS;
    }

    public function company()
    {
        return $this->belongsTo(User::class, 'company_id');
    }

    public function expert()
    {
        return $this->belongsTo(User::class, 'expert_id');
    }

    public function contract()
    {
        return $this->contract_type === 'offer'
            ? $this->belongsTo(ProjectOffer::class, 'contract_id')
            : $this->belongsTo(ServicePurchase::class, 'contract_id');
    }
}
