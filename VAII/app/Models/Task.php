<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'description', // Popis úlohy
        'deadline',    // Termín dokončenia
        'priority'     // Priorita úlohy (low, medium, high)
    ];

    /**
     * Vzťah medzi Task a User (mnoho k mnoho)
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'task_user')
            ->withPivot('status', 'solution', 'attachment') // Prístup k pivot atribútom
            ->withTimestamps();
    }
}
