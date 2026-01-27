<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExpertService extends Model
{
    use HasFactory;

    protected $table = 'expert_services';
    protected $primaryKey = 'service_id';

    protected $fillable = [
        'expert_id',
        'title',
        'category',
        'price',
        'delivery_days',
        'description',
        'is_active',
    ];

    public function expert()
    {
        return $this->belongsTo(User::class, 'expert_id');
    }
}
