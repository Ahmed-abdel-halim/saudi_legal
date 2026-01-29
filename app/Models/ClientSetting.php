<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClientSetting extends Model
{
    protected $fillable = [
        'client_id',
        'gold_standard_ratio',
    ];

    protected $casts = [
        'gold_standard_ratio' => 'decimal:2',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }
}
