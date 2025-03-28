<?php


namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Country; // Import modelu
use Illuminate\Support\Facades\DB;

class CountriesSeeder extends Seeder
{
    public function run()
    {
        $countries = [
            ['name' => 'Kazakhstan', 'region' => 'APAC'],
            ['name' => 'India', 'region' => 'APAC'],
            ['name' => 'Poland', 'region' => 'EMEA'],
            ['name' => 'Romania', 'region' => 'EMEA'],
            ['name' => 'Bulgaria', 'region' => 'EMEA'],
            ['name' => 'Slovenia', 'region' => 'EMEA'],
            ['name' => 'Latvia', 'region' => 'EMEA'],
            ['name' => 'Croatia', 'region' => 'EMEA'],
            ['name' => 'Estonia', 'region' => 'EMEA'],
            ['name' => 'Bosnia-Herzegovina', 'region' => 'EMEA'],
            ['name' => 'Hungary', 'region' => 'EMEA'],
            ['name' => 'Lithuania', 'region' => 'EMEA'],
            ['name' => 'Serbia', 'region' => 'EMEA'],
            ['name' => 'Ukraine', 'region' => 'EMEA'],
            ['name' => 'Macedonia', 'region' => 'EMEA'],
            ['name' => 'Montenegro', 'region' => 'EMEA'],
            ['name' => 'Czech Republic', 'region' => 'EMEA'],
            ['name' => 'Israel', 'region' => 'APAC'],
            ['name' => 'Türkiye', 'region' => 'APAC'],
            ['name' => 'United Arab Emirates', 'region' => 'APAC'],
            ['name' => 'Greece', 'region' => 'APAC'],
            ['name' => 'Saudi Arabia', 'region' => 'APAC'],
            ['name' => 'Qatar', 'region' => 'APAC'],
            ['name' => 'Oman', 'region' => 'APAC'],
            ['name' => 'China', 'region' => 'APAC'],
            ['name' => 'Hong Kong', 'region' => 'APAC'],
            ['name' => 'Taiwan', 'region' => 'APAC'],
            ['name' => 'Japan', 'region' => 'APAC'],
            ['name' => 'Australia', 'region' => 'APAC'],
            ['name' => 'New Zealand', 'region' => 'APAC'],
            ['name' => 'USA', 'region' => 'AMER'],
            ['name' => 'Canada', 'region' => 'AMER'],
            ['name' => 'Mexico', 'region' => 'AMER'],
            ['name' => 'Brazil', 'region' => 'AMER'],
            ['name' => 'Argentina', 'region' => 'AMER'],
            ['name' => 'Chile', 'region' => 'AMER'],
            ['name' => 'Colombia', 'region' => 'AMER'],
            ['name' => 'Peru', 'region' => 'AMER'],
            ['name' => 'Germany', 'region' => 'EMEA'],
            ['name' => 'France', 'region' => 'EMEA'],
            ['name' => 'Spain', 'region' => 'EMEA'],
            ['name' => 'Italy', 'region' => 'EMEA'],
            ['name' => 'Netherlands', 'region' => 'EMEA'],
            ['name' => 'Belgium', 'region' => 'EMEA'],
            ['name' => 'Austria', 'region' => 'EMEA'],
            ['name' => 'Switzerland', 'region' => 'EMEA'],
            ['name' => 'United Kingdom', 'region' => 'EMEA'],
            ['name' => 'Ireland', 'region' => 'EMEA'],
            ['name' => 'Denmark', 'region' => 'EMEA'],
            ['name' => 'Sweden', 'region' => 'EMEA'],
            ['name' => 'Norway', 'region' => 'EMEA'],
            ['name' => 'Finland', 'region' => 'EMEA'],
        ];

        // Vloženie do databázy
        DB::table('countries')->insert($countries);
    }
}
