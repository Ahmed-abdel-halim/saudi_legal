<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug', 'description'];

    public function tasks()
    {
        return $this->belongsToMany(AiTask::class , 'category_ai_task', 'category_id', 'ai_task_id');
    }
}
