<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'image',         // Pridáme obrázok
        'attachments'    // Pridáme prílohy (uložené ako JSON)
    ];

    protected $casts = [
        'attachments' => 'array' // Umožní Laravelu automaticky konvertovať JSON na pole
    ];
}
