<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;

use Illuminate\Database\Eloquent\Model;

class UploadedData extends Model
{
    use HasFactory;

    protected $table = 'uploaded_data'; // Názov tabuľky

    protected $fillable = [
        'source_type',
        'request',
        'description',
        'status',
        'type',
        'created',
        'finalized',
        'vendor',
        'country',
        'imported_at',
        'imported_by'
    ];

    public function countryInfo()
    {
        return $this->belongsTo(Country::class, 'country', 'name');
    }

    public function import()
    {
        return $this->belongsTo(ImportedFile::class, 'import_id');
    }

}
