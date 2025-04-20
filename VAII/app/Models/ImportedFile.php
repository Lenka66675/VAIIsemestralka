<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ImportedFile extends Model
{
    use HasFactory;

    protected $fillable = [
        'file_name',
        'path',
        'source_type',
        'uploaded_by',
        'uploaded_at',
    ];
    protected $casts = [
        'uploaded_at' => 'datetime',
    ];


    public $timestamps = false;

    // 🔁 Vzťah na uploaded_data
    public function uploadedData()
    {
        return $this->hasMany(UploadedData::class, 'import_id');
    }

    // 👤 Kto nahrával
    public function user()
    {
        return $this->belongsTo(User::class);
    }




}

