<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id', 'company_id');
    }


    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'company_id',
        'role',
        'phone',
        'trust_score',
        'gold_tasks_completed',
        'gold_tasks_failed',
        'trust_warning_issued',
        'is_banned',
        'banned_at',
        'ban_reason',
        'is_active',
        'is_active_for_hire',
        'national_id',
        'school_name',
        'expert_domain',
        'expert_specialization',
        'avatar_path',
        'job_title',
        'bio',
        // Reputation metrics
        'rating_average',
        'rating_count',
        'completion_rate',
        // Contract tracking
        'total_contracts',
        'completed_contracts',
        'cancelled_contracts',
        'disputed_contracts',
    ];

    public function conversations()
    {
        return Conversation::where('participant_1', $this->id)
            ->orWhere('participant_2', $this->id);
    }
    
    public function sentMessages()
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    public function reviews()
    {
        return $this->hasMany(Review::class, 'expert_id');
    }

    public function freelancerProfile()
    {
        return $this->hasOne(FreelancerProfile::class);
    }

    public function isFreelancer()
    {
        return $this->role === 'freelancer';
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
