<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    //
    protected $fillable = [
        'name',
        'email',
        'date',
        'description'
    ];


    public function users()
    {
        return $this->belongsToMany(User::class, 'task_user')
            ->withPivot('status', 'solution', 'attachment') // Prístup k pivot atribútom
            ->withTimestamps();
    }

}
