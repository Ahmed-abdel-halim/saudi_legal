<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug'];

    public function tasks()
    {
        return $this->belongsToMany(AiTask::class , 'ai_task_tag', 'tag_id', 'ai_task_id');
    }
}
