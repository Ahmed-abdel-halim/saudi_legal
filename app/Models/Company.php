<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory;

    protected $primaryKey = 'company_id';

    protected $fillable = [
        'name',
        'company_logo',
        'is_verified_provider',
        'cr_number',
        'industry',
        'size',
        'is_requester',
        'is_supplier',
        'status',
        // Add wallet balance to fillable list
        'wallet_balance',
    ];

    public function users()
    {
        return $this->hasMany(User::class, 'company_id', 'company_id');
    }

    // Disable timestamps if the legacy table doesn't have created_at/updated_at
    // public $timestamps = false; 
    // Assuming we might want to add them or they might exist. 
    // For now, I'll assume standard Laravel timestamps unless I see evidence otherwise.
    // Looking at the legacy code, there were no timestamps in the UPDATE query, but standard practice is to have them.
    // If errors occur, I will check the schema or disable them.
}
