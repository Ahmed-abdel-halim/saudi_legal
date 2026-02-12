<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FreelancerProfile extends Model
{
    use HasFactory;

    protected $primaryKey = 'freelancer_profile_id';

    protected $fillable = [
        'user_id',
        'title',
        'bio',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function skills()
    {
        return $this->belongsToMany(Skill::class, 'freelancer_skills', 'freelancer_profile_id', 'skill_id');
    }
}
