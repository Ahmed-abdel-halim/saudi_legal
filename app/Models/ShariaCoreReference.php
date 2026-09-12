<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShariaCoreReference extends Model
{
    protected $fillable = [
        'ref_type',
        'title',
        'author',
        'content',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];
}
