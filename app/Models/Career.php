<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Career extends Model
{
    protected $fillable = [
        'title',
        'location',
        'description',
        'is_open',
    ];

    protected $casts = [
        'is_open' => 'boolean',
    ];
}
