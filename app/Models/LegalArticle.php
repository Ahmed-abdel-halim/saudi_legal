<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LegalArticle extends Model
{
    use HasFactory;

    protected $fillable = [
        'legislation_id',
        'legislation_title',
        'article_title',
        'content',
        'reference_id'
    ];
}
