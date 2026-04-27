<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LegalJudgment extends Model
{
    use HasFactory;

    protected $fillable = [
        'case_number',
        'court_name',
        'judgment_date',
        'case_text',
        'law_system',
        'source_file',
        'metadata'
    ];

    protected $casts = [
        'metadata' => 'array',
    ];
}
