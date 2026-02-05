<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;

    protected $primaryKey = 'project_id';

    protected $fillable = [
        'title',
        'scope_description',
        'requested_duration_hours',
        'max_hourly_rate',
        'budget',
        'requester_company_id',
        'supplier_company_id',
        'status',
        'created_at',
        'updated_at',
    ];

    /**
     * Get the company that requested the project.
     */
    public function requester()
    {
        return $this->belongsTo(Company::class, 'requester_company_id', 'company_id');
    }

    /**
     * Get the company that is supplying the project.
     */
    public function supplier()
    {
        return $this->belongsTo(Company::class, 'supplier_company_id', 'company_id');
    }

    /**
     * The skills required for the project.
     */
    public function skills()
    {
        return $this->belongsToMany(Skill::class, 'project_required_skills', 'project_id', 'skill_id');
    }
}
