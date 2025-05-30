<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'description',
        'deadline',
        'priority'
    ];


    public function users()
    {
        return $this->belongsToMany(User::class, 'task_user')
            ->withPivot('status', 'solution', 'attachment')
            ->withTimestamps();
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

}
