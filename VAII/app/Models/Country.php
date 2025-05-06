<?php

namespace App\Models;


namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Http;

class Country extends Model
{
    protected $table = 'countries';

    protected $fillable = ['name', 'region', 'latitude', 'longitude'];

    public function uploadedData()
    {
        return $this->hasMany(UploadedData::class, 'country', 'name');
    }


    public static function fetchCoordinates($country)
    {
        $response = Http::withHeaders([
            'User-Agent' => 'MyLaravelApp/1.0 (myemail@example.com)'
        ])->get('https://nominatim.openstreetmap.org/search', [
            'q' => $country,
            'format' => 'json',
            'limit' => 1
        ]);

        $data = $response->json();

        if (!empty($data) && isset($data[0]['lat'], $data[0]['lon'])) {
            return [
                'lat' => $data[0]['lat'],
                'lon' => $data[0]['lon']
            ];
        }

        return null;
    }

}


