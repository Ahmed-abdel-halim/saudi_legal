<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Skill extends Model
{
    use HasFactory;

    protected $primaryKey = 'skill_id';

    protected $fillable = [
        'name',
        'name_ar',
    ];

    public function projects()
    {
        return $this->belongsToMany(Project::class, 'project_required_skills', 'skill_id', 'project_id');
    }
}
