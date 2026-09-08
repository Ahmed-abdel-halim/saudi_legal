<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BetaAccessRequest extends Model
{
    use HasFactory;

    protected $table = 'beta_access_requests';

    protected $fillable = [
        'name',
        'email',
        'phone',
        'company',
        'organization_type',
        'use_case',
        'expected_volume',
        'tech_stack',
        'notes',
        'status',
        'api_key_sandbox',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
