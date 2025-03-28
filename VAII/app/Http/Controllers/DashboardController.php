<?php

namespace App\Http\Controllers;

use App\Models\Country;
use Illuminate\Http\Request;
use App\Models\UploadedData;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use App\Models\WeeklySnapshot;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    // 1️⃣ Počet požiadaviek podľa statusu (pre koláčový graf)
    public function summary(Request $request)
    {
        $query = UploadedData::select('status', DB::raw('count(*) as total'))
            ->groupBy('status');

        // Ak je vybraný systém, pridáme podmienku
        if ($request->has('system')) {
            $query->where('source_type', $request->input('system'));
        }

        // Ak je vybraná krajina, pridáme podmienku
        if ($request->has('country')) {
            $query->where('country', $request->input('country'));
        }

        return response()->json($query->get());
    }


    // 2️⃣ Počet vytvorených vs. uzavretých žiadostí (pre stĺpcový graf)
    public function createdVsFinalized(Request $request)
    {
        $query = UploadedData::select(
            DB::raw('DATE(created) as created_date'),
            DB::raw('count(*) as created_count'),
            DB::raw('count(finalized) as finalized_count')
        )
            ->groupBy('created_date')
            ->orderBy('created_date');

        // Ak je vybraný systém, pridáme podmienku
        if ($request->has('system')) {
            $query->where('source_type', $request->input('system'));
        }

        // Ak je vybraná krajina, pridáme podmienku
        if ($request->has('country')) {
            $query->where('country', $request->input('country'));
        }

        return response()->json($query->get());
    }

    // 3️⃣ Možnosti filtrovania (systémy, krajiny, statusy)
    public function filters()
    {
        return response()->json([
            'systems' => UploadedData::select('source_type')->distinct()->pluck('source_type'),
            'countries' => UploadedData::select('country')->distinct()->pluck('country'),
            'statuses' => UploadedData::select('status')->distinct()->pluck('status')
        ]);
    }

    public function monthlySummary(Request $request)
    {
        $month = $request->input('month', now()->format('Y-m'));

        $data = UploadedData::select(
            DB::raw('DATE(created) as created_date'),
            DB::raw('COUNT(*) as created_count'),
            DB::raw('SUM(CASE WHEN DATEDIFF(NOW(), created) > 4 AND status NOT IN ("Closed", "Completed") THEN 1 ELSE 0 END) as backlog_count')
        )
            ->whereYear('created', substr($month, 0, 4)) // Vyberie rok z YYYY-MM
            ->whereMonth('created', substr($month, 5, 2)) // Vyberie mesiac z YYYY-MM
            ->groupBy('created_date')
            ->orderBy('created_date', 'asc')
            ->get();

        return response()->json($data);
    }

// 📋 2️⃣ Tabuľka backlog požiadaviek
    public function backlogTable(Request $request)
    {
        $month = $request->input('month', now()->format('Y-m'));

        $backlog = UploadedData::select(
            'request',
            'created',
            'status',
            'country',
            DB::raw('IFNULL(DATEDIFF(NOW(), created), 0) as backlog_days')
        )
            ->whereYear('created', substr($month, 0, 4))
            ->whereMonth('created', substr($month, 5, 2))
            ->whereNotIn('status', ['Closed', 'Completed'])
            ->having('backlog_days', '>', 4)
            ->orderBy('backlog_days', 'desc')
            ->get();

        return response()->json($backlog);
    }


     // Pridaj na začiatok súboru!

    public function mapData()
    {
        $data = UploadedData::select('country', DB::raw('COUNT(*) as count'))
            ->whereNotNull('country')
            ->groupBy('country')
            ->get();

        foreach ($data as $item) {
            $countryInfo = Country::where('name', $item->country)->first();

            if ($countryInfo && !is_null($countryInfo->latitude) && !is_null($countryInfo->longitude)) {
                $item->latitude = (float) $countryInfo->latitude; // 💡 Uistíme sa, že je to číslo
                $item->longitude = (float) $countryInfo->longitude;
                $item->region = $countryInfo->region;
            } else {
                $item->latitude = null; // 💡 Explicitne nastavíme na null, ak nie sú dostupné
                $item->longitude = null;
                $item->region = 'Unknown';
            }
        }

        return response()->json($data);
    }



// Funkcia na dynamické získavanie súradníc


    public function fetchCoordinates($country)
    {
        $url = "https://nominatim.openstreetmap.org/search";

        $response = Http::withHeaders([
            'User-Agent' => 'MyLaravelApp/1.0 (myemail@example.com)'
        ])->get($url, [
            'q' => $country,
            'format' => 'json',
            'limit' => 1
        ]);

        $data = $response->json();

        if (!empty($data) && isset($data[0]['lat'], $data[0]['lon'])) {
            return ['lat' => $data[0]['lat'], 'lon' => $data[0]['lon']];
        }

        return null;
    }




    public function filtersCountry()
    {
        $countries = UploadedData::select('country')
            ->distinct()
            ->pluck('country');

        // Prepojenie s tabuľkou `countries`
        $countriesWithRegions = Country::whereIn('name', $countries)->get(['name', 'region']);

        return response()->json([
            'systems' => UploadedData::select('source_type')->distinct()->pluck('source_type'),
            'countries' => $countriesWithRegions,
            'statuses' => UploadedData::select('status')->distinct()->pluck('status')
        ]);
    }


    public function getCountries()
    {
        $countries = Country::select('name', 'region')->get();
        return response()->json($countries);
    }


    public function getStats(Request $request)
    {
        $query = UploadedData::query();

        if ($request->has('region') && $request->region) {
            $countryNames = Country::where('region', $request->region)->pluck('name');
            $query->whereIn('country', $countryNames);
        }

        if ($request->has('countries') && is_array($request->countries)) {
            $query->whereIn('country', $request->countries);
        }

        return response()->json([
            'created' => $query->whereNotNull('created')->count(),
            'finalized' => $query->whereNotNull('finalized')->count(),
        ]);
    }





    public function snapshotForMonth(Request $request)
    {
        $month = $request->input('month', now()->format('Y-m'));

        $firstDayOfMonth = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        $lastDayOfMonth = Carbon::createFromFormat('Y-m', $month)->endOfMonth();

        // Získaj všetky snapshoty za mesiac
        $snapshots = WeeklySnapshot::whereBetween('snapshot_date', [$firstDayOfMonth, $lastDayOfMonth])->get();

        if ($snapshots->isEmpty()) {
            return response()->json([
                'backlog' => 0,
                'backlog_in_days' => 0,
                'avg_processing_days' => 0,
                'on_time_percentage' => 0,
            ]);
        }

        return response()->json([
            'backlog' => $snapshots->avg('backlog'),
            'backlog_in_days' => $snapshots->avg('backlog_in_days'),
            'avg_processing_days' => round($snapshots->avg('avg_processing_days'), 2),
            'on_time_percentage' => round($snapshots->avg('on_time_percentage'), 2),
        ]);
    }




}
