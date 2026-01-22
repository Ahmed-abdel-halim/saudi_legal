<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'summary',
        'content',
        'author',
        'image',
        'is_published',
        'posted_at',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'posted_at' => 'datetime',
    ];

    public function getRouteKeyName()
    {
        return 'slug';
    }
}
